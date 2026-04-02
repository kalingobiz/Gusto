<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    table: Object,
    categories: Array,
});

const cart = ref([]);
const selectedCategory = ref(props.categories?.[0]?.id);
const notes = ref('');

const cartTotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.unit_price * item.quantity, 0)
);

const currentItems = computed(() =>
    props.categories?.find(c => c.id === selectedCategory.value)?.menu_items?.filter(i => i.is_available) ?? []
);

function addItem(menuItem, variant = null) {
    const key = `${menuItem.id}-${variant?.id ?? 0}`;
    const existing = cart.value.find(c => c.key === key);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            key,
            menu_item_id: menuItem.id,
            variant_id: variant?.id ?? null,
            name: menuItem.name + (variant ? ` (${variant.name})` : ''),
            unit_price: parseFloat(menuItem.price) + (variant ? parseFloat(variant.price_modifier) : 0),
            quantity: 1,
            notes: '',
        });
    }
}

function removeItem(key) {
    const idx = cart.value.findIndex(c => c.key === key);
    if (idx >= 0) {
        if (cart.value[idx].quantity > 1) cart.value[idx].quantity--;
        else cart.value.splice(idx, 1);
    }
}

const form = useForm({});

function placeOrder() {
    form.transform(() => ({
        table_token: props.table.token,
        notes: notes.value,
        items: cart.value.map(item => ({
            menu_item_id: item.menu_item_id,
            variant_id: item.variant_id,
            quantity: item.quantity,
            notes: item.notes,
        })),
    })).post(route('orders.store'), {
        preserveScroll: true,
    });
}

function currency(v) {
    return Number(v).toFixed(2);
}
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto">
            <h1 class="text-xl font-bold text-gray-900 mb-4">New Order — Table {{ table.number }}</h1>

            <div class="flex gap-6">
                <!-- Menu Panel -->
                <div class="flex-1">
                    <!-- Category Tabs -->
                    <div class="flex gap-2 mb-4 flex-wrap">
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="selectedCategory = cat.id"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors"
                            :class="selectedCategory === cat.id ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        >
                            {{ cat.name }}
                        </button>
                    </div>

                    <!-- Items Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <button
                            v-for="item in currentItems"
                            :key="item.id"
                            @click="addItem(item)"
                            class="bg-white border border-gray-200 rounded-xl p-3 text-left hover:border-amber-400 hover:shadow-sm transition-all"
                        >
                            <div class="font-medium text-gray-900 text-sm">{{ item.name }}</div>
                            <div class="text-amber-600 font-bold mt-1">${{ currency(item.price) }}</div>
                            <!-- Variants -->
                            <div v-if="item.variants?.length" class="mt-2 space-y-1">
                                <button
                                    v-for="v in item.variants"
                                    :key="v.id"
                                    @click.stop="addItem(item, v)"
                                    class="block w-full text-xs text-left px-2 py-1 bg-amber-50 rounded-lg hover:bg-amber-100"
                                >
                                    {{ v.name }} (+${{ currency(v.price_modifier) }})
                                </button>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Cart Panel -->
                <div class="w-72 shrink-0">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm sticky top-4">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-800">Order Cart</h2>
                        </div>

                        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            <div v-for="item in cart" :key="item.key" class="px-4 py-2 flex items-center gap-2 text-sm">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 truncate">{{ item.name }}</div>
                                    <div class="text-gray-500">${{ currency(item.unit_price) }} × {{ item.quantity }}</div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="removeItem(item.key)" class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center text-lg leading-none">−</button>
                                    <span class="w-5 text-center font-medium">{{ item.quantity }}</span>
                                    <button @click="addItem({id: item.menu_item_id, price: item.unit_price, variants: []}, item.variant_id ? {id: item.variant_id, price_modifier: 0} : null)" class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-600 flex items-center justify-center text-lg leading-none">+</button>
                                </div>
                            </div>
                            <div v-if="!cart.length" class="px-4 py-6 text-center text-sm text-gray-400">No items yet</div>
                        </div>

                        <div class="px-4 py-3 border-t border-gray-100 space-y-3">
                            <textarea
                                v-model="notes"
                                placeholder="Order notes (optional)"
                                rows="2"
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 resize-none focus:outline-none focus:ring-1 focus:ring-amber-400"
                            />
                            <div class="flex items-center justify-between font-semibold">
                                <span class="text-gray-700">Total</span>
                                <span class="text-lg text-gray-900">${{ currency(cartTotal) }}</span>
                            </div>
                            <button
                                @click="placeOrder"
                                :disabled="!cart.length || form.processing"
                                class="w-full bg-amber-500 text-white py-2.5 rounded-xl font-semibold hover:bg-amber-600 disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Placing...' : 'Place Order' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
