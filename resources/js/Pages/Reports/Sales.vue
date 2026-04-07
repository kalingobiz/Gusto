<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportNav from '@/Components/ReportNav.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

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

// ─── SVG Revenue Line Chart ───────────────────────────────────────────────────
const chartW = 560;
const chartH = 120;
const chartPad = { top: 10, right: 16, bottom: 20, left: 0 };

const chartData = computed(() => {
    if (!props.byDay?.length) return null;
    const revenues  = props.byDay.map(d => Number(d.revenue));
    const maxRev    = Math.max(...revenues, 1);
    const innerW    = chartW - chartPad.left - chartPad.right;
    const innerH    = chartH - chartPad.top  - chartPad.bottom;
    const step      = revenues.length > 1 ? innerW / (revenues.length - 1) : innerW;

    const points = revenues.map((rev, i) => ({
        x: chartPad.left + i * step,
        y: chartPad.top + innerH - (rev / maxRev) * innerH,
        rev,
        date: props.byDay[i].date,
        orders: props.byDay[i].orders,
    }));

    const lineStr = points.map(p => `${p.x},${p.y}`).join(' ');

    // Area path: line down to bottom-right → bottom-left → back up
    const areaStr = `M${points[0].x},${points[0].y} `
        + points.slice(1).map(p => `L${p.x},${p.y}`).join(' ')
        + ` L${points[points.length - 1].x},${chartH - chartPad.bottom}`
        + ` L${points[0].x},${chartH - chartPad.bottom} Z`;

    return { points, lineStr, areaStr, maxRev };
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">
                    Sales <span class="text-[var(--brand)]">Report</span>
                </h1>
                <ReportNav />
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

            <!-- Revenue Line Chart -->
            <div class="glass-card p-6" v-if="chartData">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Revenue Over Time</div>
                    <div class="text-[10px] font-bold text-[var(--text-muted)]">{{ byDay?.length }} days</div>
                </div>
                <div class="relative overflow-hidden">
                    <svg
                        :viewBox="`0 0 ${chartW} ${chartH}`"
                        class="w-full"
                        :style="`height:${chartH}px`"
                        preserveAspectRatio="none"
                    >
                        <defs>
                            <linearGradient id="rev-gradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f97316" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#f97316" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <!-- Grid lines -->
                        <line
                            v-for="n in 4"
                            :key="n"
                            :x1="0" :x2="chartW"
                            :y1="chartPad.top + ((chartH - chartPad.top - chartPad.bottom) / 4) * n"
                            :y2="chartPad.top + ((chartH - chartPad.top - chartPad.bottom) / 4) * n"
                            stroke="currentColor"
                            class="text-[var(--border)]"
                            stroke-width="0.5"
                            stroke-dasharray="4,4"
                        />
                        <!-- Area fill -->
                        <path :d="chartData.areaStr" fill="url(#rev-gradient)" />
                        <!-- Line -->
                        <polyline
                            :points="chartData.lineStr"
                            fill="none"
                            stroke="#f97316"
                            stroke-width="2.5"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                        />
                        <!-- Data points with tooltip -->
                        <g v-for="(pt, i) in chartData.points" :key="i" class="group">
                            <circle :cx="pt.x" :cy="pt.y" r="5" fill="#f97316" class="opacity-0 group-hover:opacity-100 transition-opacity" />
                            <circle :cx="pt.x" :cy="pt.y" r="3" fill="#f97316" />
                            <title>{{ pt.date }}: {{ currency(pt.rev) }} Birr · {{ pt.orders }} orders</title>
                        </g>
                    </svg>
                    <!-- X-axis labels -->
                    <div v-if="byDay?.length <= 14" class="flex justify-between px-0 mt-1">
                        <span v-for="d in byDay" :key="d.date" class="text-[9px] text-[var(--text-muted)] font-bold truncate">
                            {{ d.date.slice(5) }}
                        </span>
                    </div>
                </div>
            </div>
            <div v-else-if="!byDay?.length" class="glass-card p-8 text-center text-[var(--text-muted)] italic text-sm">
                No daily data for this period.
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
                            <tr v-for="(item, i) in byItem" :key="item.id" class="hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-black italic text-[var(--text-muted)] w-5">{{ String(i+1).padStart(2,'0') }}</span>
                                        <div>
                                            <div class="font-bold text-[var(--text-strong)]">{{ item.name }}</div>
                                            <div class="text-[10px] text-[var(--text-muted)] uppercase">{{ item.category }}</div>
                                        </div>
                                    </div>
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
                            <div v-for="p in byPaymentMethod" :key="p.method" class="px-5 py-3.5 flex items-center justify-between hover:bg-[var(--bg-surface)] transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[var(--brand)]/10 border border-[var(--brand)]/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-[var(--brand)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold text-[var(--text-strong)] capitalize">{{ p.method.replace('_', ' ') }}</span>
                                </div>
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
                        <div class="divide-y divide-[var(--border)] max-h-52 overflow-y-auto custom-scrollbar">
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
