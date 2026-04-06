<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    from: String,
    to: String,
    summary: Object,
    byDay: Array,
    byItem: Array,
    byPaymentMethod: Array,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.sales'), { preserveScroll: true });
}

function currency(v) { return Number(v || 0).toFixed(2); }
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Sales Report</h1>
            <div class="flex gap-2 text-xs flex-wrap">
                    <Link :href="route('admin.reports.bom-variance')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">BOM Variance</Link>
                    <Link :href="route('admin.reports.voids')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">Void Log</Link>
                    <Link :href="route('admin.reports.audit')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">Audit Trail</Link>
                    <Link :href="route('admin.reports.stock')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">Stock</Link>
                    <Link :href="route('admin.reports.item-performance')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">Item Performance</Link>
                    <Link :href="route('admin.reports.hourly-sales')" class="btn-secondary px-3 py-1.5 font-bold uppercase tracking-widest">Hourly</Link>
                </div>
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                <span class="text-[var(--text-muted)] text-xs font-bold uppercase">to</span>
                <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="glass-card p-6 border-l-4 border-emerald-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Revenue</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ currency(summary?.revenue) }} Birr</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-[var(--brand)]">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Paid Orders</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ summary?.order_count ?? 0 }}</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-blue-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Tax Collected</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ currency(summary?.tax_collected) }} Birr</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Items -->
                <div class="glass-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                        <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Top Items by Revenue</h2>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                                <th class="text-left px-5 py-3">Item</th>
                                <th class="text-right px-5 py-3">Qty</th>
                                <th class="text-right px-5 py-3">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="item in byItem" :key="item.id" class="hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-[var(--text-strong)]">{{ item.name }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)] uppercase">{{ item.category }}</div>
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-[var(--text-base)]">{{ item.total_qty }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-emerald-500">{{ currency(item.total_revenue) }} Birr</td>
                            </tr>
                            <tr v-if="!byItem?.length">
                                <td colspan="3" class="px-5 py-8 text-center text-[var(--text-muted)] italic text-sm">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Methods + Daily -->
                <div class="space-y-6">
                    <div class="glass-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                            <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Payment Methods</h2>
                        </div>
                        <div class="divide-y divide-[var(--border)]">
                            <div v-for="p in byPaymentMethod" :key="p.method" class="px-5 py-3 flex items-center justify-between hover:bg-[var(--bg-surface)] transition-colors">
                                <span class="text-sm font-bold text-[var(--text-strong)] capitalize">{{ p.method.replace('_', ' ') }}</span>
                                <div class="text-right">
                                    <div class="font-black font-mono text-emerald-500">{{ currency(p.total) }} Birr</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-bold">{{ p.count }} transactions</div>
                                </div>
                            </div>
                            <div v-if="!byPaymentMethod?.length" class="px-5 py-6 text-center text-sm text-[var(--text-muted)] italic">No payments</div>
                        </div>
                    </div>

                    <div class="glass-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                            <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Daily Breakdown</h2>
                        </div>
                        <div class="divide-y divide-[var(--border)] max-h-52 overflow-y-auto">
                            <div v-for="d in byDay" :key="d.date" class="px-5 py-2.5 flex items-center justify-between hover:bg-[var(--bg-surface)] transition-colors">
                                <span class="text-sm text-[var(--text-muted)] font-medium">{{ d.date }}</span>
                                <div class="text-right">
                                    <div class="font-bold font-mono text-[var(--text-strong)]">{{ currency(d.revenue) }} Birr</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-bold">{{ d.orders }} orders</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
