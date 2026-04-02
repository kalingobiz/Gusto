<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentOrders: Array,
    topItems: Array,
    lowStockIngredients: Array,
});

function currency(val) {
    return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const statusColors = {
    draft: 'bg-gray-500/10 text-gray-500 border-gray-500/20',
    confirmed: 'bg-blue-500/10 text-blue-500 border-blue-500/20',
    in_progress: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
    ready: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
    served: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
    paid: 'bg-emerald-500 border-emerald-600 text-white shadow-sm',
    voided: 'bg-red-500/10 text-red-500 border-red-500/20',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-8 max-w-7xl mx-auto">
            <!-- Welcome Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black italic tracking-tight">Executive Overview</h1>
                    <p class="text-[var(--text-muted)] text-sm">Real-time performance metrics and operational pulse.</p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-strong)]">System Live</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Revenue Card -->
                <div class="glass-card p-6 border-l-4 border-emerald-500 shadow-lg shadow-emerald-500/5 relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Today Revenue</div>
                    <div class="text-3xl font-black italic text-[var(--text-strong)]">${{ currency(stats?.today_revenue) }}</div>
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-[0.03] group-hover:scale-110 transition-transform">💰</div>
                </div>

                <!-- Orders Count -->
                <div class="glass-card p-6 border-l-4 border-[var(--brand)] shadow-lg shadow-[var(--brand-glow)] relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Orders</div>
                    <div class="text-3xl font-black italic text-[var(--text-strong)]">{{ stats?.today_orders }}</div>
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-[0.03] group-hover:scale-110 transition-transform">📋</div>
                </div>

                <!-- Active Sessions -->
                <div class="glass-card p-6 border-l-4 border-blue-500 shadow-lg shadow-blue-500/5 relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Active Floor</div>
                    <div class="text-3xl font-black italic text-blue-500">{{ stats?.active_orders }}</div>
                    <div class="absolute -right-4 -bottom-4 text-6xl opacity-[0.03] group-hover:scale-110 transition-transform">🍽️</div>
                </div>

                <!-- Low Stock -->
                <div class="glass-card p-6 border-l-4 border-red-500 shadow-lg shadow-red-500/5 relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Critical Stock</div>
                    <div class="text-3xl font-black italic" :class="stats?.low_stock > 0 ? 'text-red-500 animate-pulse' : 'text-[var(--text-strong)]'">
                        {{ stats?.low_stock }}
                    </div>
                </div>

                <!-- Voids -->
                <div class="glass-card p-6 border-l-4 border-gray-400 shadow-lg relative overflow-hidden group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Voids</div>
                    <div class="text-3xl font-black italic text-[var(--text-strong)]">{{ stats?.today_voids }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Orders Panel -->
                <div class="lg:col-span-2 glass-card overflow-hidden flex flex-col">
                    <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-surface)] flex items-center justify-between">
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Recent Order Stream</h2>
                            <p class="text-[10px] text-[var(--text-muted)] font-bold">Latest transactions across the floor.</p>
                        </div>
                        <Link :href="route('tables.index')" class="text-[10px] font-black text-[var(--brand)] hover:underline uppercase tracking-widest">Floor Map →</Link>
                    </div>
                    
                    <div class="divide-y divide-[var(--border)] overflow-y-auto max-h-[500px]">
                        <div v-for="order in recentOrders" :key="order.id" class="px-6 py-4 flex items-center justify-between group hover:bg-[var(--bg-surface)] transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-lg shadow-inner group-hover:border-[var(--brand)] transition-colors">
                                    🪑
                                </div>
                                <div>
                                    <div class="text-sm font-black italic text-[var(--text-strong)]">Table {{ order.restaurant_table?.number }}</div>
                                    <div class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-tighter">Order #{{ order.id }} • {{ order.restaurant_table?.capacity }}p</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-right">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm font-black italic text-[var(--text-strong)]">${{ currency(order.total) }}</span>
                                    <div class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase border tracking-widest" :class="statusColors[order.status]">
                                        {{ order.status.replace('_', ' ') }}
                                    </div>
                                </div>
                                <Link :href="route('orders.show', order.id)" class="btn-icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </Link>
                            </div>
                        </div>
                        <div v-if="!recentOrders?.length" class="p-20 text-center text-[var(--text-muted)] italic text-sm">
                            No active traffic on the floor today.
                        </div>
                    </div>
                </div>

                <!-- Performance Column -->
                <div class="space-y-8">
                    <!-- Top Items -->
                    <div class="glass-card overflow-hidden">
                        <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                            <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Popular Today</h2>
                        </div>
                        <div class="divide-y divide-[var(--border)]">
                            <div v-for="(item, index) in topItems" :key="item.id" class="px-6 py-4 flex items-center justify-between group hover:bg-[var(--bg-surface)]">
                                <div class="flex items-center gap-3">
                                    <div class="text-xs font-black italic text-[var(--text-muted)] w-4">0{{ index + 1 }}</div>
                                    <span class="text-sm font-bold text-[var(--text-strong)]">{{ item.name }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-black italic text-[var(--brand)]">×{{ item.total_qty }}</div>
                                    <div class="text-[10px] font-bold text-[var(--text-muted)]">${{ currency(item.total_revenue) }}</div>
                                </div>
                            </div>
                            <div v-if="!topItems?.length" class="p-10 text-center text-[var(--text-muted)] italic text-xs">Waiting for sales...</div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div v-if="lowStockIngredients?.length" class="glass-card overflow-hidden border-2 border-red-500/20 bg-red-500/5 animate-in fade-in zoom-in duration-500">
                        <div class="px-6 py-4 border-b border-red-500/20 bg-red-500/10 flex items-center gap-3">
                            <span class="animate-pulse">⚠️</span>
                            <h2 class="text-[10px] font-black uppercase tracking-widest text-red-500">Critical Shortage</h2>
                        </div>
                        <div class="divide-y divide-red-500/10">
                            <div v-for="ing in lowStockIngredients" :key="ing.id" class="px-6 py-3 flex items-center justify-between group hover:bg-red-500/10 transition-all">
                                <span class="text-xs font-bold text-red-400">{{ ing.name }}</span>
                                <div class="text-right">
                                    <span class="text-xs font-black italic text-red-500">{{ Number(ing.current_stock).toFixed(1) }}</span>
                                    <span class="text-[10px] font-bold text-red-400 ml-1 uppercase">{{ ing.unit }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-3">
                            <Link :href="route('admin.ingredients.index')" class="w-full block text-center py-2 bg-red-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:scale-[1.02] transition-transform">Restock Material Now</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
