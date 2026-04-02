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

function varianceClass(row) {
    if (row.is_flagged) return 'bg-red-50';
    if (Math.abs(row.variance_pct) > 2) return 'bg-yellow-50';
    return '';
}

function pctClass(pct) {
    if (Math.abs(pct) > 5) return 'text-red-600 font-bold';
    if (Math.abs(pct) > 2) return 'text-yellow-600 font-medium';
    return 'text-green-600';
}
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">BOM Variance Report</h1>
                    <p class="text-sm text-gray-500">Compares theoretical stock consumption (from orders) against actual stock movements. Variances >5% are flagged as potential discrepancies.</p>
                </div>
                <div v-if="flagged > 0" class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-semibold text-sm">
                    ⚠️ {{ flagged }} ingredient{{ flagged > 1 ? 's' : '' }} flagged
                </div>
            </div>

            <!-- Date filter -->
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-3 flex-wrap">
                <div class="flex items-center gap-2 text-sm">
                    <label class="text-gray-600">From</label>
                    <input v-model="filterForm.from" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <label class="text-gray-600">To</label>
                    <input v-model="filterForm.to" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                </div>
                <button @click="applyFilter" class="px-4 py-1.5 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600">Apply</button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Ingredient</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Theoretical Used</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Total Intake</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Current Stock</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Variance</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Variance %</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="row in ingredients" :key="row.id" :class="varianceClass(row)">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ row.name }}</div>
                                <div class="text-xs text-gray-400">{{ row.unit }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ Number(row.theoretical_used).toFixed(4) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ Number(row.total_intake).toFixed(4) }}</td>
                            <td class="px-4 py-3 text-right" :class="row.is_low_stock ? 'text-red-600 font-medium' : 'text-gray-700'">
                                {{ Number(row.current_stock).toFixed(4) }}
                                <span v-if="row.is_low_stock" class="text-xs ml-1">⚠️</span>
                            </td>
                            <td class="px-4 py-3 text-right" :class="pctClass(row.variance_pct)">{{ Number(row.variance).toFixed(4) }}</td>
                            <td class="px-4 py-3 text-right" :class="pctClass(row.variance_pct)">{{ row.variance_pct }}%</td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="row.is_flagged" class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-medium">🚨 Alert</span>
                                <span v-else-if="Math.abs(row.variance_pct) > 2" class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs">Watch</span>
                                <span v-else class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">OK</span>
                            </td>
                        </tr>
                        <tr v-if="!ingredients?.length">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">No data for selected period</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
