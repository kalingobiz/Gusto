<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String, to: String, logs: Object,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.audit'), { preserveScroll: true });
}

const actionColors = {
    created:        'bg-blue-100 text-blue-700',
    status_changed: 'bg-yellow-100 text-yellow-700',
    voided:         'bg-red-100 text-red-700',
    qty_modified:   'bg-orange-100 text-orange-700',
    price_modified: 'bg-purple-100 text-purple-700',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-bold text-gray-900">Audit Trail</h1>
            <p class="text-sm text-gray-500">Complete immutable log of every order item action. Use this to investigate discrepancies.</p>

            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <span class="text-gray-400 text-sm">to</span>
                <input v-model="filterForm.to" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <button @click="applyFilter" class="px-4 py-1.5 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600">Apply</button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Time</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Item</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Table</th>
                            <th class="text-center px-4 py-2 font-medium text-gray-600">Action</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Status Change</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">By</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="log in logs?.data" :key="log.id" class="text-xs hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500">{{ new Date(log.created_at).toLocaleString() }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ log.order_item?.menu_item?.name }}</td>
                            <td class="px-4 py-2 text-gray-600">T{{ log.order_item?.order?.restaurant_table?.number }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="actionColors[log.action] ?? 'bg-gray-100 text-gray-600'">{{ log.action }}</span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                <span v-if="log.from_status">{{ log.from_status }} → {{ log.to_status }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-2 text-gray-700">{{ log.user?.name ?? 'Customer' }}</td>
                            <td class="px-4 py-2 text-gray-400 font-mono">{{ log.ip_address }}</td>
                        </tr>
                        <tr v-if="!logs?.data?.length">
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">No audit records found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
