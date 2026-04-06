<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import KitchenLayout from '@/Layouts/KitchenLayout.vue';

const props = defineProps({
    orders: Array,
});

// Newest first (LIFO) — already sorted by backend, just ensure reversed on arrival
const orders = ref([...(props.orders ?? [])].reverse());
const usePolling = import.meta.env.VITE_USE_POLLING === 'true';
let pollTimer = null;
let echoChannel = null;

function elapsedMinutes(createdAt) {
    return Math.floor((Date.now() - new Date(createdAt).getTime()) / 60000);
}

function urgencyClass(createdAt) {
    const m = elapsedMinutes(createdAt);
    if (m < 5)  return 'border-green-500 bg-gray-900';
    if (m < 10) return 'border-yellow-500 bg-gray-900';
    if (m < 15) return 'border-orange-500 bg-orange-950/30';
    return 'border-red-500 bg-red-950/30 animate-pulse';
}

// Full status config for kitchen display
const statusConfig = {
    pending:  { label: 'Pending',  cls: 'bg-gray-700 text-gray-300',     btn: 'Start Cooking', nextStatus: 'cooking',  btnCls: 'bg-yellow-700 hover:bg-yellow-600' },
    cooking:  { label: 'Cooking',  cls: 'bg-amber-600/80 text-white',     btn: 'Mark Ready',    nextStatus: 'ready',    btnCls: 'bg-blue-700 hover:bg-blue-600' },
    ready:    { label: 'Ready',    cls: 'bg-blue-700 text-white',         btn: 'Delivered ✓',  nextStatus: 'done',     btnCls: 'bg-green-700 hover:bg-green-600' },
    done:     { label: 'Done',     cls: 'bg-green-700 text-white',        btn: null,            nextStatus: null,       btnCls: '' },
    voided:   { label: 'Voided',   cls: 'bg-red-900 text-red-300 line-through', btn: null,     nextStatus: null,       btnCls: '' },
};

function itemStatusClass(status) {
    return statusConfig[status]?.cls ?? 'bg-gray-700 text-gray-300';
}

async function updateItemStatus(item, status) {
    await fetch(route('kitchen.item.update', item.id), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
        body: JSON.stringify({ kitchen_status: status }),
    });

    // Optimistic update
    for (const order of orders.value) {
        const found = order.items?.find(i => i.id === item.id);
        if (found) { found.kitchen_status = status; break; }
    }

    // Remove order from kitchen view if all items are done/voided/delivered
    orders.value = orders.value.filter(order => {
        const active = order.items?.filter(i => !['done', 'voided'].includes(i.kitchen_status));
        return active?.length > 0;
    });
}

function addOrUpdateOrder(newOrder) {
    const idx = orders.value.findIndex(o => o.id === newOrder.order.id);
    if (idx >= 0) {
        orders.value[idx] = { ...orders.value[idx], ...newOrder.order };
    } else {
        // LIFO: newest at the TOP
        orders.value.unshift(newOrder.order);
    }
    try { new Audio('/sounds/bell.mp3').play(); } catch {}
}

onMounted(() => {
    if (usePolling) {
        pollTimer = setInterval(async () => {
            const res = await fetch(route('kitchen.pending'));
            const data = await res.json();
            // Reverse to show newest first
            orders.value = [...data].reverse();
        }, 5000);
    } else if (window.Echo) {
        echoChannel = window.Echo.channel('kitchen')
            .listen('.order.placed', (data) => addOrUpdateOrder(data))
            .listen('.order-item.status-changed', (data) => {
                for (const order of orders.value) {
                    const item = order.items?.find(i => i.id === data.item.id);
                    if (item) { item.kitchen_status = data.item.kitchen_status; break; }
                }
            });
    }
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
    echoChannel?.stopListening('.order.placed');
});
</script>

<template>
    <KitchenLayout>
        <div v-if="!orders.length" class="flex flex-col items-center justify-center h-64 text-gray-500 gap-3">
            <div class="text-5xl">✓</div>
            <div class="text-xl font-bold">{{ __('Kitchen is clear — no active orders') }}</div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div
                v-for="order in orders"
                :key="order.id"
                class="border-2 rounded-xl overflow-hidden"
                :class="urgencyClass(order.created_at)"
            >
                <!-- Card Header -->
                <div class="px-4 py-2 border-b border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black text-amber-400">T{{ order.restaurant_table?.number ?? order.table_number }}</span>
                        <span class="text-xs text-gray-400">#{{ order.id }}</span>
                        <span v-if="order.source === 'customer_qr'" class="text-xs bg-blue-800 text-blue-200 px-1.5 py-0.5 rounded">QR</span>
                    </div>
                    <div class="text-sm font-mono text-gray-400">
                        {{ elapsedMinutes(order.created_at) }}m
                    </div>
                </div>

                <!-- Items -->
                <div class="divide-y divide-gray-800 p-1">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-lg mb-1 transition-all duration-300"
                        :class="itemStatusClass(item.kitchen_status)"
                    >
                        <span class="text-lg font-bold w-6 shrink-0">{{ item.quantity }}×</span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">{{ item.menu_item?.name ?? item.name }}</div>
                            <div v-if="item.notes" class="text-xs opacity-75 mt-0.5">{{ item.notes }}</div>
                            <!-- Status badge -->
                            <div class="text-[10px] font-bold uppercase tracking-wider opacity-60 mt-0.5">
                                {{ __(statusConfig[item.kitchen_status]?.label) }}
                            </div>
                        </div>

                        <!-- Action button — step through statuses -->
                        <div class="flex flex-col gap-1 shrink-0" v-if="item.kitchen_status !== 'voided' && item.kitchen_status !== 'done'">
                            <button
                                v-if="statusConfig[item.kitchen_status]?.btn"
                                @click="updateItemStatus(item, statusConfig[item.kitchen_status].nextStatus)"
                                class="text-xs text-white px-2 py-1.5 rounded font-bold transition-all"
                                :class="statusConfig[item.kitchen_status].btnCls"
                            >{{ __(statusConfig[item.kitchen_status].btn) }}</button>
                        </div>
                    </div>
                </div>

                <!-- Footer notes -->
                <div v-if="order.notes" class="px-4 py-2 text-xs text-amber-300 border-t border-gray-700">
                    📝 {{ order.notes }}
                </div>
            </div>
        </div>
    </KitchenLayout>
</template>
