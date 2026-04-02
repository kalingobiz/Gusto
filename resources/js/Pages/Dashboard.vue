<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentOrders: Array,
    topItems: Array,
    lowStockIngredients: Array,
});

function currency(val) {
    return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const statusColors = {
    draft: 'bg-gray-100 text-gray-700',
    confirmed: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    ready: 'bg-green-100 text-green-700',
    served: 'bg-purple-100 text-purple-700',
    paid: 'bg-emerald-100 text-emerald-700',
    voided: 'bg-red-100 text-red-700',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Today Revenue</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">${{ currency(stats?.today_revenue) }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Today Orders</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ stats?.today_orders }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Active Orders</div>
                    <div class="text-2xl font-bold text-amber-600 mt-1">{{ stats?.active_orders }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Low Stock</div>
                    <div class="text-2xl font-bold mt-1" :class="stats?.low_stock > 0 ? 'text-red-600' : 'text-gray-900'">{{ stats?.low_stock }}</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="text-sm text-gray-500">Today Voids</div>
                    <div class="text-2xl font-bold mt-1" :class="stats?.today_voids > 0 ? 'text-red-600' : 'text-gray-900'">{{ stats?.today_voids }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Orders -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800">Recent Orders</h2>
                        <Link :href="route('tables.index')" class="text-sm text-amber-600 hover:underline">View all</Link>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="order in recentOrders" :key="order.id" class="px-4 py-3 flex items-center justify-between text-sm">
                            <div>
                                <span class="font-medium text-gray-900">Table {{ order.restaurant_table?.number }}</span>
                                <span class="ml-2 text-gray-500">#{{ order.id }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="statusColors[order.status]">{{ order.status }}</span>
                                <span class="text-gray-700 font-medium">${{ currency(order.total) }}</span>
                            </div>
                        </div>
                        <div v-if="!recentOrders?.length" class="px-4 py-6 text-center text-sm text-gray-400">No orders today</div>
                    </div>
                </div>

                <!-- Side column -->
                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-800">Top Items Today</h2>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div v-for="item in topItems" :key="item.id" class="px-4 py-2 flex items-center justify-between text-sm">
                                <span class="text-gray-800">{{ item.name }}</span>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900">×{{ item.total_qty }}</div>
                                    <div class="text-xs text-gray-500">${{ currency(item.total_revenue) }}</div>
                                </div>
                            </div>
                            <div v-if="!topItems?.length" class="px-4 py-4 text-center text-sm text-gray-400">No sales yet</div>
                        </div>
                    </div>

                    <div v-if="lowStockIngredients?.length" class="bg-red-50 rounded-xl border border-red-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-red-200">
                            <h2 class="font-semibold text-red-700">⚠️ Low Stock Alert</h2>
                        </div>
                        <div class="divide-y divide-red-100">
                            <div v-for="ing in lowStockIngredients" :key="ing.id" class="px-4 py-2 flex items-center justify-between text-sm">
                                <span class="text-red-800">{{ ing.name }}</span>
                                <span class="text-red-600 font-medium">{{ ing.current_stock }} {{ ing.unit }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
