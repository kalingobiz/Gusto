<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

function currency(v) { return Number(v || 0).toFixed(2); }
</script>

<template>
    <CustomerLayout :tableNumber="order.restaurant_table?.number">
        <div class="flex flex-col items-center text-center px-6 py-12 space-y-5">
            <div class="text-6xl animate-bounce">🎉</div>
            <h1 class="text-2xl font-bold text-gray-900">Order Placed!</h1>
            <p class="text-gray-500">Order #{{ order.id }} has been sent to the kitchen. A staff member will serve you shortly.</p>

            <div class="w-full bg-white rounded-2xl border border-gray-200 overflow-hidden mt-4">
                <div class="px-4 py-3 border-b border-gray-100 text-left font-semibold text-gray-700 text-sm">Your Order Summary</div>
                <div class="divide-y divide-gray-50">
                    <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between px-4 py-3 text-sm">
                        <span class="text-gray-800">×{{ item.quantity }} {{ item.menu_item?.name }}</span>
                        <span class="font-semibold text-gray-900">${{ currency(item.line_total) }}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
                    <span class="font-semibold text-gray-700">Total</span>
                    <span class="text-lg font-black text-gray-900">${{ currency(order.total) }}</span>
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-2">Payment is made at the cashier or when our staff comes to your table.</p>
        </div>
    </CustomerLayout>
</template>
