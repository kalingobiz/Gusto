<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportNav from '@/Components/ReportNav.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String,
    to: String,
    byHour: Array,
    peakHour: Object,
    totalOrders: Number,
    totalRevenue: Number,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.hourly-sales'), { preserveScroll: true });
}

function currency(v) { return Number(v || 0).toFixed(2); }

const maxRevenue = Math.max(...(props.byHour?.length ? props.byHour.map(h => h.revenue) : [1]));

function barHeight(revenue) {
    if (!maxRevenue || maxRevenue <= 0) return 0;
    return Math.max(4, Math.round((revenue / maxRevenue) * 100));
}

function formatHour(h) {
    const hour = parseInt(h);
    if (hour === 0) return '12am';
    if (hour < 12) return `${hour}am`;
    if (hour === 12) return '12pm';
    return `${hour - 12}pm`;
}


</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Hourly <span class="text-[var(--brand)]">Sales</span></h1>
                <ReportNav />
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                <span class="text-[var(--text-muted)] text-xs font-bold uppercase">to</span>
                <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="glass-card p-6 border-l-4 border-emerald-500">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Revenue</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ currency(totalRevenue) }} Birr</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-[var(--brand)]">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Orders</div>
                    <div class="text-3xl font-heading font-black text-[var(--text-strong)]">{{ totalOrders ?? 0 }}</div>
                </div>
                <div class="glass-card p-6 border-l-4 border-purple-500" v-if="peakHour">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Peak Hour</div>
                    <div class="text-3xl font-heading font-black text-purple-400">{{ formatHour(peakHour.hour) }}</div>
                    <div class="text-xs text-[var(--text-muted)] font-bold mt-1">{{ peakHour.orders }} orders · {{ currency(peakHour.revenue) }} Birr</div>
                </div>
            </div>

            <!-- Bar chart -->
            <div class="glass-card p-6">
                <div class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)] mb-6">Revenue by Hour of Day</div>
                <div class="flex items-end gap-1 h-48 overflow-x-auto pb-2" style="scrollbar-width: thin;">
                    <div
                        v-for="h in byHour"
                        :key="h.hour"
                        class="flex flex-col items-center gap-1 min-w-[40px] flex-1 group"
                    >
                        <div class="text-[9px] font-black text-[var(--text-muted)] transition-all group-hover:text-[var(--brand)]">
                            {{ h.orders }}
                        </div>
                        <div
                            class="w-full rounded-t-md transition-all duration-300 group-hover:opacity-90 cursor-default"
                            :class="peakHour && h.hour === peakHour.hour ? 'bg-purple-500' : 'bg-[var(--brand)]'"
                            :style="{ height: barHeight(h.revenue) + '%' }"
                            :title="`${formatHour(h.hour)}: ${h.orders} orders, ${currency(h.revenue)} Birr`"
                        ></div>
                        <div class="text-[10px] font-bold text-[var(--text-muted)] group-hover:text-[var(--text-strong)] transition-colors whitespace-nowrap">
                            {{ formatHour(h.hour) }}
                        </div>
                    </div>
                    <div v-if="!byHour?.length" class="w-full text-center text-[var(--text-muted)] italic text-sm py-16">
                        No data for this period
                    </div>
                </div>
            </div>

            <!-- Detailed table -->
            <div class="glass-card overflow-hidden">
                <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Hourly Breakdown</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                                <th class="text-left px-5 py-3">Hour</th>
                                <th class="text-right px-5 py-3">Orders</th>
                                <th class="text-right px-5 py-3">Revenue</th>
                                <th class="text-left px-5 py-3">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="h in byHour" :key="h.hour"
                                class="hover:bg-[var(--bg-surface)] transition-colors"
                                :class="peakHour && h.hour === peakHour.hour ? 'bg-purple-500/5' : ''">
                                <td class="px-5 py-3 font-bold text-[var(--text-strong)] flex items-center gap-2">
                                    <span v-if="peakHour && h.hour === peakHour.hour" class="text-xs text-purple-400 font-black">PEAK</span>
                                    {{ formatHour(h.hour) }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-[var(--text-base)]">{{ h.orders }}</td>
                                <td class="px-5 py-3 text-right font-mono font-black text-emerald-500">{{ currency(h.revenue) }} Birr</td>
                                <td class="px-5 py-3 w-40">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-[var(--bg-surface)] rounded-full border border-[var(--border)] overflow-hidden">
                                            <div class="h-full rounded-full"
                                                :class="peakHour && h.hour === peakHour.hour ? 'bg-purple-500' : 'bg-[var(--brand)]'"
                                                :style="{ width: barHeight(h.revenue) + '%' }"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-[var(--text-muted)]">{{ barHeight(h.revenue) }}%</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!byHour?.length">
                                <td colspan="4" class="px-5 py-8 text-center text-[var(--text-muted)] italic text-sm">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
