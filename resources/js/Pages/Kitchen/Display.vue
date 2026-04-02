<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import KitchenLayout from '@/Layouts/KitchenLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    orders: Array,
});

const orders = ref(props.orders ?? []);
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

function itemStatusClass(status) {
    return {
        pending:     'bg-gray-700 text-gray-300',
        in_progress: 'bg-yellow-600 text-white',
        done:        'bg-green-700 text-white',
        voided:      'bg-red-900 text-red-300 line-through',
    }[status] ?? 'bg-gray-700 text-gray-300';
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

    // Remove order if all items done/voided
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
        orders.value.unshift(newOrder.order);
    }
    // Play notification sound
    try { new Audio('/sounds/bell.mp3').play(); } catch {}
}

onMounted(() => {
    if (usePolling) {
        pollTimer = setInterval(async () => {
            const res = await fetch(route('kitchen.pending'));
            const data = await res.json();
            orders.value = data;
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
        <div v-if="!orders.length" class="flex items-center justify-center h-64 text-gray-500 text-xl">
            No active orders — kitchen is clear ✓
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
                        class="flex items-center gap-2 px-3 py-2.5 rounded-lg"
                        :class="itemStatusClass(item.kitchen_status)"
                    >
                        <span class="text-lg font-bold w-6 shrink-0">{{ item.quantity }}×</span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">{{ item.menu_item?.name ?? item.name }}</div>
                            <div v-if="item.notes" class="text-xs opacity-75 mt-0.5">{{ item.notes }}</div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-col gap-1 shrink-0" v-if="item.kitchen_status !== 'voided'">
                            <button
                                v-if="item.kitchen_status === 'pending'"
                                @click="updateItemStatus(item, 'in_progress')"
                                class="text-xs bg-yellow-700 hover:bg-yellow-600 text-white px-2 py-1 rounded"
                            >Start</button>
                            <button
                                v-if="item.kitchen_status === 'in_progress'"
                                @click="updateItemStatus(item, 'done')"
                                class="text-xs bg-green-700 hover:bg-green-600 text-white px-2 py-1 rounded"
                            >Done ✓</button>
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
