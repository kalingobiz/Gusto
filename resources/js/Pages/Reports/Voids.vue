<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    from: String, to: String, voids: Object, byUser: Array,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.voids'), { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Void Log</h1>
                <div class="flex gap-2">
                    <Link :href="route('admin.reports.sales')" class="text-sm text-gray-500 hover:underline">← Sales</Link>
                    <Link :href="route('admin.reports.audit')" class="text-sm text-gray-500 hover:underline">Audit Trail →</Link>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <span class="text-gray-400 text-sm">to</span>
                <input v-model="filterForm.to" type="date" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" />
                <button @click="applyFilter" class="px-4 py-1.5 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600">Apply</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <!-- By User (theft proxy) -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800 text-sm">Voids by Staff</div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="u in byUser" :key="u.name" class="px-4 py-2.5 flex items-center justify-between text-sm">
                            <span class="text-gray-900">{{ u.name }}</span>
                            <span class="font-bold" :class="u.void_count > 5 ? 'text-red-600' : 'text-gray-700'">{{ u.void_count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Void records -->
                <div class="lg:col-span-3 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">Item</th>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">Table</th>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">Reason</th>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">By</th>
                                <th class="text-left px-4 py-2 font-medium text-gray-600">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="v in voids?.data" :key="v.id">
                                <td class="px-4 py-2.5">
                                    <div class="font-medium text-gray-900">{{ v.order_item?.menu_item?.name }}</div>
                                    <div class="text-xs text-gray-400">×{{ v.order_item?.quantity }}</div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600">T{{ v.order_item?.order?.restaurant_table?.number }}</td>
                                <td class="px-4 py-2.5 text-red-700">{{ v.reason }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ v.user?.name }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ new Date(v.created_at).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!voids?.data?.length">
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No voids in this period</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
