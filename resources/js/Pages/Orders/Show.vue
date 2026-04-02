<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const order = ref(props.order);

const statusColors = {
    pending:     'bg-gray-100 text-gray-600',
    in_progress: 'bg-yellow-100 text-yellow-700',
    done:        'bg-green-100 text-green-700',
    voided:      'bg-red-100 text-red-600 line-through',
};

const orderStatusColors = {
    draft: 'bg-gray-100 text-gray-700', confirmed: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-yellow-100 text-yellow-700', ready: 'bg-green-100 text-green-700',
    served: 'bg-purple-100 text-purple-700', paid: 'bg-emerald-100 text-emerald-700',
    voided: 'bg-red-100 text-red-700',
};

function currency(v) { return Number(v || 0).toFixed(2); }

// Void item
const voidForm = useForm({ reason: '' });
const voidingItem = ref(null);

function startVoid(item) {
    voidingItem.value = item;
    voidForm.reason = '';
}

function confirmVoid() {
    voidForm.post(route('orders.void-item', [order.value.id, voidingItem.value.id]), {
        preserveScroll: true,
        onSuccess: () => { voidingItem.value = null; router.reload(); },
    });
}

// Payment
const payForm = useForm({ order_id: order.value.id, method: 'cash', amount: order.value.total, reference: '', bank_name: '' });
const showPayment = ref(false);

function submitPayment() {
    payForm.post(route('payments.store'), {
        preserveScroll: true,
        onSuccess: () => { showPayment.value = false; router.reload(); },
    });
}

// Real-time order updates
let echoChannel;
onMounted(() => {
    if (window.Echo) {
        echoChannel = window.Echo.channel(`orders.${order.value.id}`)
            .listen('.order.status-changed', (data) => {
                order.value.status = data.status;
                order.value.total = data.total;
            })
            .listen('.order-item.status-changed', (data) => {
                const item = order.value.items?.find(i => i.id === data.item.id);
                if (item) item.kitchen_status = data.item.kitchen_status;
            });
    }
});
onUnmounted(() => echoChannel?.stopListening('.order.status-changed'));
</script>

<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Order #{{ order.id }}</h1>
                    <div class="text-sm text-gray-500">Table {{ order.restaurant_table?.number }} · {{ order.source === 'customer_qr' ? '📱 QR Order' : '🖥️ Staff Order' }}</div>
                </div>
                <span class="px-3 py-1.5 rounded-full text-sm font-medium capitalize" :class="orderStatusColors[order.status]">{{ order.status }}</span>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Items</div>
                <div class="divide-y divide-gray-50">
                    <div v-for="item in order.items" :key="item.id" class="px-4 py-3 flex items-center gap-3">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900" :class="item.kitchen_status === 'voided' ? 'line-through text-gray-400' : ''">
                                {{ item.menu_item?.name }}
                                <span v-if="item.variant" class="text-gray-500 text-sm"> ({{ item.variant.name }})</span>
                            </div>
                            <div v-if="item.notes" class="text-xs text-gray-500 mt-0.5">Note: {{ item.notes }}</div>
                            <div v-if="item.void_log" class="text-xs text-red-500 mt-0.5">Voided: {{ item.void_log.reason }}</div>
                        </div>
                        <div class="text-sm text-gray-600">×{{ item.quantity }}</div>
                        <div class="w-20 text-right font-medium text-gray-900">${{ currency(item.line_total) }}</div>
                        <span class="w-24 text-center text-xs px-2 py-1 rounded-full" :class="statusColors[item.kitchen_status]">{{ item.kitchen_status }}</span>
                        <button
                            v-if="!['voided', 'done'].includes(item.kitchen_status) && !['paid', 'voided'].includes(order.status)"
                            @click="startVoid(item)"
                            class="text-xs text-red-400 hover:text-red-600"
                        >Void</button>
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total</span>
                    <span class="text-lg font-bold text-gray-900">${{ currency(order.total) }}</span>
                </div>
            </div>

            <!-- Payment Info -->
            <div v-if="order.payment" class="bg-emerald-50 rounded-xl border border-emerald-200 px-4 py-3">
                <div class="text-sm font-semibold text-emerald-700 mb-1">Payment Recorded</div>
                <div class="text-sm text-emerald-600">
                    {{ order.payment.method === 'cash' ? 'Cash' : 'Bank Transfer' }} ·
                    ${{ currency(order.payment.amount) }}
                    <span v-if="order.payment.reference"> · Ref: {{ order.payment.reference }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="!['paid', 'voided'].includes(order.status)" class="flex gap-3">
                <button
                    v-if="order.status === 'ready'"
                    @click="$inertia.patch(route('orders.served', order.id))"
                    class="flex-1 bg-purple-500 text-white py-2.5 rounded-xl font-semibold hover:bg-purple-600"
                >Mark Served</button>
                <button
                    v-if="['served', 'ready', 'confirmed', 'in_progress'].includes(order.status)"
                    @click="showPayment = true"
                    class="flex-1 bg-emerald-500 text-white py-2.5 rounded-xl font-semibold hover:bg-emerald-600"
                >Record Payment</button>
            </div>

            <!-- Payment Modal -->
            <div v-if="showPayment" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-900">Record Payment</h2>
                    <div>
                        <label class="text-sm text-gray-600">Amount</label>
                        <input v-model="payForm.amount" type="number" step="0.01" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Method</label>
                        <select v-model="payForm.method" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div v-if="payForm.method === 'bank_transfer'" class="space-y-2">
                        <div>
                            <label class="text-sm text-gray-600">Bank Name</label>
                            <input v-model="payForm.bank_name" type="text" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Bank name" />
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Reference #</label>
                            <input v-model="payForm.reference" type="text" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Transaction reference" />
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button @click="showPayment = false" class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl font-medium">Cancel</button>
                        <button @click="submitPayment" :disabled="payForm.processing" class="flex-1 bg-emerald-500 text-white py-2.5 rounded-xl font-semibold hover:bg-emerald-600">Confirm</button>
                    </div>
                </div>
            </div>

            <!-- Void Modal -->
            <div v-if="voidingItem" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-900">Void Item</h2>
                    <p class="text-sm text-gray-600">Void <strong>{{ voidingItem.menu_item?.name }}</strong>?</p>
                    <div>
                        <label class="text-sm text-gray-600">Reason (required)</label>
                        <input v-model="voidForm.reason" type="text" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-400" placeholder="e.g. Wrong item ordered" />
                        <div v-if="voidForm.errors.reason" class="text-xs text-red-500 mt-1">{{ voidForm.errors.reason }}</div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button @click="voidingItem = null" class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl font-medium">Cancel</button>
                        <button @click="confirmVoid" :disabled="!voidForm.reason || voidForm.processing" class="flex-1 bg-red-500 text-white py-2.5 rounded-xl font-semibold hover:bg-red-600 disabled:opacity-50">Void Item</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
