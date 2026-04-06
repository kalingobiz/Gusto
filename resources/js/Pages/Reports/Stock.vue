<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    ingredients: Array,
    lowCount: Number,
    criticalCount: Number,
});

function stockStatus(ing) {
    if (ing.current_stock <= 0) return { label: 'Out of Stock', cls: 'bg-red-500/10 text-red-500 border-red-500/20' };
    if (ing.current_stock <= ing.low_stock_threshold) return { label: 'Low Stock', cls: 'bg-amber-500/10 text-amber-500 border-amber-500/20' };
    return { label: 'In Stock', cls: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' };
}

function stockPct(ing) {
    if (!ing.low_stock_threshold || ing.low_stock_threshold === 0) return 100;
    return Math.min(100, Math.round((ing.current_stock / (ing.low_stock_threshold * 3)) * 100));
}

const reportLinks = [
    { label: 'Sales',            route: 'admin.reports.sales' },
    { label: 'BOM Variance',     route: 'admin.reports.bom-variance' },
    { label: 'Void Log',         route: 'admin.reports.voids' },
    { label: 'Audit Trail',      route: 'admin.reports.audit' },
    { label: 'Item Performance', route: 'admin.reports.item-performance' },
    { label: 'Hourly Sales',     route: 'admin.reports.hourly-sales' },
];
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Stock <span class="text-[var(--brand)]">Levels</span></h1>
                <div class="flex gap-2 text-xs flex-wrap">
                    <Link v-for="l in reportLinks" :key="l.route" :href="route(l.route)"
                        class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">{{ l.label }}</Link>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="glass-card p-6 border-l-4 border-emerald-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Ingredients</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ ingredients?.length ?? 0 }}</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-amber-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Low Stock</div>
                    <div class="text-3xl font-heading font-black text-amber-500">{{ lowCount ?? 0 }}</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-red-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Out of Stock</div>
                    <div class="text-3xl font-heading font-black text-red-500">{{ criticalCount ?? 0 }}</div>
                </div>
            </div>

            <!-- Ingredients table -->
            <div class="glass-card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">All Ingredients</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                                <th class="text-left px-5 py-3">Ingredient</th>
                                <th class="text-right px-5 py-3">Current Stock</th>
                                <th class="text-right px-5 py-3">Unit</th>
                                <th class="text-right px-5 py-3">Low Threshold</th>
                                <th class="text-center px-5 py-3">Level</th>
                                <th class="text-center px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="ing in ingredients" :key="ing.id"
                                class="hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-5 py-3 font-bold text-[var(--text-strong)]">{{ ing.name }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold"
                                    :class="ing.current_stock <= 0 ? 'text-red-500' : ing.current_stock <= ing.low_stock_threshold ? 'text-amber-500' : 'text-emerald-500'">
                                    {{ ing.current_stock }}
                                </td>
                                <td class="px-5 py-3 text-right text-[var(--text-muted)]">{{ ing.unit }}</td>
                                <td class="px-5 py-3 text-right text-[var(--text-muted)]">{{ ing.low_stock_threshold ?? '—' }}</td>
                                <td class="px-5 py-3 w-36">
                                    <div class="h-2 bg-[var(--bg-surface)] rounded-full border border-[var(--border)] overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                            :class="ing.current_stock <= 0 ? 'bg-red-500' : ing.current_stock <= ing.low_stock_threshold ? 'bg-amber-500' : 'bg-emerald-500'"
                                            :style="{ width: stockPct(ing) + '%' }">
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border"
                                        :class="stockStatus(ing).cls">
                                        {{ stockStatus(ing).label }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!ingredients?.length">
                                <td colspan="6" class="px-5 py-8 text-center text-[var(--text-muted)] italic text-sm">No ingredients found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
