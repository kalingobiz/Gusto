<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import KitchenLayout from '@/Layouts/KitchenLayout.vue';

const props = defineProps({ orders: Array });

// Newest first (LIFO)
const orders = ref([...(props.orders ?? [])].reverse());
const usePolling = import.meta.env.VITE_USE_POLLING === 'true';
let pollTimer  = null;
let echoChannel = null;

function elapsedMinutes(createdAt) {
    return Math.floor((Date.now() - new Date(createdAt).getTime()) / 60000);
}

// Card border + glow — all using CSS-compatible classes (no hardcoded bg-gray-900)
function urgencyBorderClass(createdAt) {
    const m = elapsedMinutes(createdAt);
    if (m < 5)  return 'border-emerald-500/50 shadow-[0_0_20px_rgba(16,185,129,0.08)]';
    if (m < 10) return 'border-amber-500/60  shadow-[0_0_20px_rgba(245,158,11,0.10)]';
    if (m < 15) return 'border-orange-500/70 shadow-[0_0_20px_rgba(249,115,22,0.12)]';
    return 'border-red-500 shadow-[0_0_30px_rgba(239,68,68,0.2)] animate-pulse';
}

function urgencyHeaderClass(createdAt) {
    const m = elapsedMinutes(createdAt);
    if (m < 5)  return 'border-emerald-500/20 bg-emerald-500/5';
    if (m < 10) return 'border-amber-500/20   bg-amber-500/5';
    if (m < 15) return 'border-orange-500/20  bg-orange-500/5';
    return 'border-red-500/30 bg-red-500/10';
}

function timerClass(createdAt) {
    const m = elapsedMinutes(createdAt);
    if (m < 5)  return 'text-emerald-400';
    if (m < 10) return 'text-amber-400';
    if (m < 15) return 'text-orange-400';
    return 'text-red-400 font-black animate-pulse';
}

function tableNumClass(createdAt) {
    const m = elapsedMinutes(createdAt);
    if (m < 10) return 'text-[var(--text-strong)]';
    if (m < 15) return 'text-orange-400';
    return 'text-red-400';
}

// Item status config — uses CSS vars and themed classes
const statusConfig = {
    pending: {
        label: 'Pending',
        cls: 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)]',
        btn: 'Start Cooking',
        nextStatus: 'cooking',
        btnCls: 'text-xs font-black uppercase tracking-wider px-3 py-1.5 rounded-lg bg-[var(--brand)] text-white hover:bg-[var(--brand-hover)] transition-colors',
    },
    cooking: {
        label: 'Cooking',
        cls: 'bg-amber-500/10 text-amber-400 border-amber-500/25',
        btn: 'Mark Ready',
        nextStatus: 'ready',
        btnCls: 'text-xs font-black uppercase tracking-wider px-3 py-1.5 rounded-lg text-white transition-colors',
        btnStyle: 'background:var(--gradient-success)',
    },
    ready: {
        label: 'Ready ✓',
        cls: 'bg-blue-500/10 text-blue-400 border-blue-500/25',
        btn: 'Delivered',
        nextStatus: 'done',
        btnCls: 'text-xs font-black uppercase tracking-wider px-3 py-1.5 rounded-lg text-white transition-colors',
        btnStyle: 'background:var(--gradient-purple)',
    },
    done: {
        label: 'Delivered',
        cls: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        btn: null,
        nextStatus: null,
        btnCls: '',
    },
    voided: {
        label: 'Voided',
        cls: 'bg-[var(--danger)]/8 text-[var(--danger)] border-[var(--danger)]/20 opacity-50',
        btn: null,
        nextStatus: null,
        btnCls: '',
    },
};

function itemStatusCls(status) {
    return statusConfig[status]?.cls ?? 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)]';
}

async function updateItemStatus(item, status) {
    await fetch(route('kitchen.item.update', item.id), {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: JSON.stringify({ kitchen_status: status }),
    });

    // Optimistic update
    for (const order of orders.value) {
        const found = order.items?.find(i => i.id === item.id);
        if (found) { found.kitchen_status = status; break; }
    }

    // Remove order from view if all items are done/voided
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
    try { new Audio('/sounds/bell.mp3').play(); } catch {}
}

onMounted(() => {
    if (usePolling) {
        pollTimer = setInterval(async () => {
            const res  = await fetch(route('kitchen.pending'));
            const data = await res.json();
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
        <!-- Empty state -->
        <div v-if="!orders.length" class="flex flex-col items-center justify-center h-64 gap-4">
            <div class="w-20 h-20 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="text-center">
                <div class="text-xl font-black text-[var(--text-strong)]">{{ __('Kitchen is clear') }}</div>
                <div class="text-sm text-[var(--text-muted)] font-medium mt-1">{{ __('No active orders — waiting on floor.') }}</div>
            </div>
        </div>

        <!-- Orders grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div
                v-for="order in orders"
                :key="order.id"
                class="glass-card border-2 overflow-hidden flex flex-col"
                :class="urgencyBorderClass(order.created_at)"
            >
                <!-- Card Header -->
                <div
                    class="px-4 py-3 border-b flex items-center justify-between"
                    :class="urgencyHeaderClass(order.created_at)"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M19.5 3v11.25a2.25 2.25 0 01-2.25 2.25H15m-6.75 0v3a2.25 2.25 0 002.25 2.25h3a2.25 2.25 0 002.25-2.25v-3M3.75 7.5h16.5"/>
                            </svg>
                            <span class="text-xl font-black" :class="tableNumClass(order.created_at)">
                                T{{ order.restaurant_table?.number ?? order.table_number }}
                            </span>
                        </div>
                        <span class="text-xs font-bold text-[var(--text-muted)] font-mono">#{{ order.id }}</span>
                        <span v-if="order.source === 'customer_qr'"
                            class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-[var(--info)]/10 text-[var(--info)] border border-[var(--info)]/20">
                            QR
                        </span>
                    </div>
                    <div class="text-sm font-black font-mono" :class="timerClass(order.created_at)">
                        {{ elapsedMinutes(order.created_at) }}m
                    </div>
                </div>

                <!-- Items list -->
                <div class="flex-1 divide-y divide-[var(--border)] p-2 space-y-1">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border transition-all duration-300"
                        :class="itemStatusCls(item.kitchen_status)"
                    >
                        <!-- Quantity bubble -->
                        <div class="w-8 h-8 rounded-lg bg-black/10 flex items-center justify-center font-black text-sm flex-shrink-0">
                            {{ item.quantity }}×
                        </div>

                        <!-- Item info -->
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm truncate leading-tight">
                                {{ item.menu_item?.name ?? item.name }}
                            </div>
                            <div v-if="item.notes" class="text-[10px] opacity-75 mt-0.5 font-medium">
                                📝 {{ item.notes }}
                            </div>
                            <div class="text-[10px] font-black uppercase tracking-wider opacity-60 mt-0.5">
                                {{ __(statusConfig[item.kitchen_status]?.label) }}
                            </div>
                        </div>

                        <!-- Action button -->
                        <div v-if="item.kitchen_status !== 'voided' && item.kitchen_status !== 'done'" class="flex-shrink-0">
                            <button
                                v-if="statusConfig[item.kitchen_status]?.btn"
                                @click="updateItemStatus(item, statusConfig[item.kitchen_status].nextStatus)"
                                :class="statusConfig[item.kitchen_status].btnCls"
                                :style="statusConfig[item.kitchen_status].btnStyle"
                            >
                                {{ __(statusConfig[item.kitchen_status].btn) }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order notes footer -->
                <div v-if="order.notes" class="px-4 py-2.5 text-xs font-medium text-amber-400 border-t" :class="urgencyHeaderClass(order.created_at)">
                    <span class="font-black uppercase tracking-wider opacity-60 mr-1">Note:</span>{{ order.notes }}
                </div>
            </div>
        </div>
    </KitchenLayout>
</template>
