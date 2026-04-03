<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String,
    to: String,
    ingredients: Array,
    flagged: Number,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.bom-variance'), { preserveScroll: true });
}

function varianceRowClass(row) {
    if (row.is_flagged) return 'bg-[var(--danger)]/5';
    if (Math.abs(row.variance_pct) > 2) return 'bg-[var(--warning)]/5';
    return '';
}

function pctClass(pct) {
    if (Math.abs(pct) > 5) return 'text-[var(--danger)] font-bold';
    if (Math.abs(pct) > 2) return 'text-[var(--warning)] font-medium';
    return 'text-[var(--success)]';
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-[var(--text-strong)]">BOM Variance Report</h1>
                    <p class="text-sm text-[var(--text-muted)]">Compares theoretical stock consumption against actual movements. Variances &gt;5% are flagged.</p>
                </div>
                <div v-if="flagged > 0" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--danger)]/10 border border-[var(--danger)]/30 text-[var(--danger)] font-bold text-sm">
                    <span class="w-2 h-2 rounded-full bg-[var(--danger)] animate-pulse"></span>
                    {{ flagged }} ingredient{{ flagged > 1 ? 's' : '' }} flagged
                </div>
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">From</label>
                    <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">To</label>
                    <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                </div>
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <!-- Table -->
            <div class="glass-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black border-b border-[var(--border)]">
                            <th class="text-left px-5 py-4">Ingredient</th>
                            <th class="text-right px-5 py-4">Theoretical Used</th>
                            <th class="text-right px-5 py-4">Total Intake</th>
                            <th class="text-right px-5 py-4">Current Stock</th>
                            <th class="text-right px-5 py-4">Variance</th>
                            <th class="text-right px-5 py-4">Variance %</th>
                            <th class="text-center px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="row in ingredients" :key="row.id" class="hover:bg-[var(--bg-surface)] transition-colors" :class="varianceRowClass(row)">
                            <td class="px-5 py-4">
                                <div class="font-bold text-[var(--text-strong)]">{{ row.name }}</div>
                                <div class="text-[10px] text-[var(--text-muted)] uppercase">{{ row.unit }}</div>
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-[var(--text-base)]">{{ Number(row.theoretical_used).toFixed(4) }}</td>
                            <td class="px-5 py-4 text-right font-mono text-[var(--text-base)]">{{ Number(row.total_intake).toFixed(4) }}</td>
                            <td class="px-5 py-4 text-right font-mono" :class="row.is_low_stock ? 'text-[var(--danger)] font-bold' : 'text-[var(--text-base)]'">
                                {{ Number(row.current_stock).toFixed(4) }}
                                <span v-if="row.is_low_stock" class="text-[10px] ml-1">⚠️</span>
                            </td>
                            <td class="px-5 py-4 text-right font-mono" :class="pctClass(row.variance_pct)">{{ Number(row.variance).toFixed(4) }}</td>
                            <td class="px-5 py-4 text-right font-mono" :class="pctClass(row.variance_pct)">{{ row.variance_pct }}%</td>
                            <td class="px-5 py-4 text-center">
                                <span v-if="row.is_flagged" class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--danger)]/10 text-[var(--danger)] border border-[var(--danger)]/20">Alert</span>
                                <span v-else-if="Math.abs(row.variance_pct) > 2" class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--warning)]/10 text-[var(--warning)] border border-[var(--warning)]/20">Watch</span>
                                <span v-else class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[var(--success)]/10 text-[var(--success)] border border-[var(--success)]/20">OK</span>
                            </td>
                        </tr>
                        <tr v-if="!ingredients?.length">
                            <td colspan="7" class="px-5 py-10 text-center text-[var(--text-muted)] italic text-sm">No data for selected period</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
