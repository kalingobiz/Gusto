<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

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
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
                <div class="flex gap-2 text-sm">
                    <Link :href="route('admin.reports.bom-variance')" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">BOM Variance</Link>
                    <Link :href="route('admin.reports.voids')" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Void Log</Link>
                    <Link :href="route('admin.reports.audit')" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Audit Trail</Link>
                </div>
            </div>

            <!-- Date filter -->
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <span class="text-gray-400 text-sm">to</span>
                <input v-model="filterForm.to" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <button @click="applyFilter" class="px-4 py-1.5 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600">Apply</button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="text-sm text-gray-500">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">${{ currency(summary?.revenue) }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="text-sm text-gray-500">Paid Orders</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ summary?.order_count ?? 0 }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-gray-200">
                    <div class="text-sm text-gray-500">Tax Collected</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">${{ currency(summary?.tax_collected) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Top Items -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800">Top Items by Revenue</div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">Item</th>
                                <th class="text-right px-4 py-2 font-medium text-gray-600">Qty</th>
                                <th class="text-right px-4 py-2 font-medium text-gray-600">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in byItem" :key="item.id">
                                <td class="px-4 py-2">
                                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                                    <div class="text-xs text-gray-400">{{ item.category }}</div>
                                </td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ item.total_qty }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-900">${{ currency(item.total_revenue) }}</td>
                            </tr>
                            <tr v-if="!byItem?.length">
                                <td colspan="3" class="px-4 py-4 text-center text-gray-400 text-sm">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Methods + Daily -->
                <div class="space-y-4">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800">Payment Methods</div>
                        <div class="divide-y divide-gray-50">
                            <div v-for="p in byPaymentMethod" :key="p.method" class="px-4 py-3 flex items-center justify-between text-sm">
                                <div class="font-medium text-gray-900 capitalize">{{ p.method.replace('_', ' ') }}</div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">${{ currency(p.total) }}</div>
                                    <div class="text-xs text-gray-400">{{ p.count }} transactions</div>
                                </div>
                            </div>
                            <div v-if="!byPaymentMethod?.length" class="px-4 py-4 text-center text-sm text-gray-400">No payments</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800">Daily Breakdown</div>
                        <div class="divide-y divide-gray-50 max-h-52 overflow-y-auto">
                            <div v-for="d in byDay" :key="d.date" class="px-4 py-2 flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ d.date }}</span>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900">${{ currency(d.revenue) }}</div>
                                    <div class="text-xs text-gray-400">{{ d.orders }} orders</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
