<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String,
    to: String,
    items: Array,
    totalRevenue: Number,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.item-performance'), { preserveScroll: true });
}

function currency(v) { return Number(v || 0).toFixed(2); }

function pct(revenue) {
    if (!props.totalRevenue || props.totalRevenue === 0) return 0;
    return Math.round((revenue / props.totalRevenue) * 100);
}

const reportLinks = [
    { label: 'Sales',        route: 'admin.reports.sales' },
    { label: 'BOM Variance', route: 'admin.reports.bom-variance' },
    { label: 'Void Log',     route: 'admin.reports.voids' },
    { label: 'Audit Trail',  route: 'admin.reports.audit' },
    { label: 'Stock',        route: 'admin.reports.stock' },
    { label: 'Hourly Sales', route: 'admin.reports.hourly-sales' },
];
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Item <span class="text-[var(--brand)]">Performance</span></h1>
                <div class="flex gap-2 text-xs flex-wrap">
                    <Link v-for="l in reportLinks" :key="l.route" :href="route(l.route)"
                        class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">{{ l.label }}</Link>
                </div>
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                <span class="text-[var(--text-muted)] text-xs font-bold uppercase">to</span>
                <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <!-- Summary -->
            <div class="glass-card p-6 border-l-4 border-[var(--brand)]">
                <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Revenue (period)</div>
                <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ currency(totalRevenue) }} Birr</div>
            </div>

            <!-- Items table -->
            <div class="glass-card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Items — Ranked by Revenue</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                                <th class="text-left px-5 py-3">#</th>
                                <th class="text-left px-5 py-3">Item</th>
                                <th class="text-left px-5 py-3">Category</th>
                                <th class="text-right px-5 py-3">Qty Sold</th>
                                <th class="text-right px-5 py-3">Revenue</th>
                                <th class="text-left px-5 py-3 min-w-[140px]">% of Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="(item, i) in items" :key="item.id"
                                class="hover:bg-[var(--bg-surface)] transition-colors"
                                :class="i === 0 ? 'bg-[var(--brand)]/5' : ''">
                                <td class="px-5 py-3">
                                    <span v-if="i === 0" class="text-lg">🥇</span>
                                    <span v-else-if="i === 1" class="text-lg">🥈</span>
                                    <span v-else-if="i === 2" class="text-lg">🥉</span>
                                    <span v-else class="text-[var(--text-muted)] font-mono text-xs">{{ i + 1 }}</span>
                                </td>
                                <td class="px-5 py-3 font-bold text-[var(--text-strong)]">{{ item.name }}</td>
                                <td class="px-5 py-3 text-[var(--text-muted)] text-xs uppercase font-bold">{{ item.category }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-[var(--text-base)]">{{ item.total_qty }}</td>
                                <td class="px-5 py-3 text-right font-mono font-black text-emerald-500">{{ currency(item.total_revenue) }} Birr</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-[var(--bg-surface)] rounded-full border border-[var(--border)] overflow-hidden">
                                            <div class="h-full bg-[var(--brand)] rounded-full" :style="{ width: pct(item.total_revenue) + '%' }"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-[var(--text-muted)] w-8 text-right">{{ pct(item.total_revenue) }}%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!items?.length">
                                <td colspan="6" class="px-5 py-8 text-center text-[var(--text-muted)] italic text-sm">No data for this period</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
