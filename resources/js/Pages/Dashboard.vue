<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentOrders: Array,
    topItems: Array,
    lowStockIngredients: Array,
    recentAdjustments: Array,
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
                    <h1 class="text-4xl font-heading font-black tracking-tight text-[var(--text-strong)]">{{ __('Executive Overview') }}</h1>
                    <p class="text-[var(--text-muted)] font-medium">{{ __('Real-time performance metrics and operational pulse.') }}</p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-strong)]">{{ __('System Live') }}</span>
                </div>
            </div>

            <!-- Low Stock Alert Banner -->
            <div v-if="lowStockIngredients?.length" class="glass-card border-2 border-[var(--danger)]/30 bg-[var(--danger)]/5 px-5 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-[var(--danger)] animate-pulse flex-shrink-0"></span>
                    <p class="text-sm font-bold text-[var(--danger)]">
                        {{ lowStockIngredients.length }} {{ __('ingredients below reorder level — immediate restock recommended.') }}
                    </p>
                </div>
                <Link :href="route('admin.ingredients.index')" class="btn-primary px-4 py-2 text-xs flex-shrink-0">
                    {{ __('Review Stock') }}
                </Link>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <!-- Revenue Card -->
                <div class="glass-card p-5 relative overflow-hidden group btn-haptic border-t-2 border-emerald-500/40">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">{{ __('Today Revenue') }}</div>
                    <div class="text-2xl font-heading font-black text-[var(--text-strong)] truncate">${{ currency(stats?.today_revenue) }}</div>
                </div>

                <!-- Orders Count -->
                <div class="glass-card p-5 relative overflow-hidden group btn-haptic border-t-2 border-[var(--brand)]/40">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-[var(--brand)]/10 border border-[var(--brand)]/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[var(--brand)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">{{ __('Total Orders') }}</div>
                    <div class="text-2xl font-heading font-black text-[var(--text-strong)]">{{ stats?.today_orders }}</div>
                </div>

                <!-- Active Floor -->
                <div class="glass-card p-5 relative overflow-hidden group btn-haptic border-t-2 border-blue-500/40">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">{{ __('Active Floor') }}</div>
                    <div class="text-2xl font-heading font-black text-blue-500">{{ stats?.active_orders }}</div>
                </div>

                <!-- Critical Stock -->
                <div class="glass-card p-5 relative overflow-hidden group btn-haptic border-t-2 border-red-500/40">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">{{ __('Critical Stock') }}</div>
                    <div class="text-2xl font-heading font-black" :class="stats?.low_stock > 0 ? 'text-red-500 animate-pulse' : 'text-[var(--text-strong)]'">
                        {{ stats?.low_stock }}
                    </div>
                </div>

                <!-- Total Voids -->
                <div class="glass-card p-5 relative overflow-hidden group border-t-2 border-gray-400/30">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">{{ __('Today Voids') }}</div>
                    <div class="text-2xl font-heading font-black text-[var(--text-strong)]">{{ stats?.today_voids }}</div>
                </div>

                <!-- Pending Audits -->
                <Link :href="route('admin.stocktakes.index')" class="glass-card p-5 relative overflow-hidden group btn-haptic border-t-2 border-amber-500/40 block">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <svg class="w-3.5 h-3.5 text-[var(--text-muted)] group-hover:text-[var(--brand)] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Pending Audits</div>
                    <div class="text-2xl font-heading font-black" :class="stats?.pending_audits > 0 ? 'text-amber-500' : 'text-[var(--text-strong)]'">
                        {{ stats?.pending_audits ?? 0 }}
                    </div>
                </Link>
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

                    <!-- Recent Adjustments -->
                    <div v-if="recentAdjustments?.length" class="glass-card overflow-hidden">
                        <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-surface)] flex items-center justify-between">
                            <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Recent Adjustments</h2>
                            <Link :href="route('admin.ingredients.index')" class="text-[10px] font-black text-[var(--brand)] hover:underline uppercase tracking-widest">All →</Link>
                        </div>
                        <div class="divide-y divide-[var(--border)]">
                            <div v-for="adj in recentAdjustments" :key="adj.id" class="px-6 py-3 flex items-center justify-between group hover:bg-[var(--bg-surface)] transition-all">
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-[var(--text-strong)] truncate">{{ adj.ingredient?.name }}</div>
                                    <div class="text-[10px] font-bold text-[var(--text-muted)] capitalize">{{ adj.type?.replace('_', ' ') }}</div>
                                </div>
                                <span class="text-xs font-black font-mono ml-3 flex-shrink-0" :class="adj.quantity >= 0 ? 'text-[var(--success)]' : 'text-[var(--danger)]'">
                                    {{ adj.quantity >= 0 ? '+' : '' }}{{ Number(adj.quantity).toFixed(2) }}
                                </span>
                            </div>
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
