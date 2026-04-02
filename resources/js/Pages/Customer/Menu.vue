<script setup>
import { ref, computed } from 'vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    table: Object,
    tableToken: String,
    categories: Array,
});

const selectedCategory = ref(props.categories?.[0]?.id);
const cart = ref([]);
const showCart = ref(false);
const orderNotes = ref('');
const selectedItem = ref(null);

const cartCount = computed(() => cart.value.reduce((s, i) => s + i.quantity, 0));
const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.unit_price * i.quantity, 0));

const currentItems = computed(() =>
    props.categories?.find(c => c.id === selectedCategory.value)?.menu_items ?? []
);

function addItem(menuItem, variant = null) {
    const key = `${menuItem.id}-${variant?.id ?? 0}`;
    const price = parseFloat(menuItem.price) + (variant ? parseFloat(variant.price_modifier) : 0);
    const existing = cart.value.find(c => c.key === key);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            key,
            menu_item_id: menuItem.id,
            variant_id: variant?.id ?? null,
            name: menuItem.name + (variant ? ` — ${variant.name}` : ''),
            unit_price: price,
            quantity: 1,
            notes: '',
        });
    }
    selectedItem.value = null;
}

function removeOne(key) {
    const idx = cart.value.findIndex(c => c.key === key);
    if (idx >= 0) {
        if (cart.value[idx].quantity > 1) cart.value[idx].quantity--;
        else cart.value.splice(idx, 1);
    }
}

function addOne(key) {
    const item = cart.value.find(c => c.key === key);
    if (item) item.quantity++;
}

const form = useForm({});
const submitted = ref(false);

function placeOrder() {
    form.transform(() => ({
        items: cart.value.map(i => ({
            menu_item_id: i.menu_item_id,
            variant_id: i.variant_id,
            quantity: i.quantity,
            notes: i.notes,
        })),
        notes: orderNotes.value,
    })).post(route('customer.place', props.tableToken), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            showCart.value = false;
            submitted.value = true;
        },
    });
}

function currency(v) { return Number(v).toFixed(2); }
</script>

<template>
    <CustomerLayout :tableNumber="table.number">
        <!-- Order submitted confirmation -->
        <div v-if="submitted" class="flex flex-col items-center justify-center min-h-[60vh] text-center px-6 py-12 space-y-4">
            <div class="text-6xl">✅</div>
            <h1 class="text-2xl font-bold text-gray-900">Order Received!</h1>
            <p class="text-gray-500">Your order has been sent to the kitchen. Please wait — a staff member will assist you.</p>
            <button @click="submitted = false" class="mt-4 px-6 py-3 bg-amber-500 text-white rounded-xl font-semibold hover:bg-amber-600">Order More</button>
        </div>

        <template v-else>
            <!-- Category tabs (sticky below header) -->
            <div class="sticky top-14 z-10 bg-amber-50 px-4 py-2 flex gap-2 overflow-x-auto scrollbar-hide border-b border-amber-100">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCategory = cat.id"
                    class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                    :class="selectedCategory === cat.id ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-gray-700 border border-gray-200'"
                >{{ cat.name }}</button>
            </div>

            <!-- Items grid -->
            <div class="px-4 pt-4 pb-4 grid grid-cols-2 gap-3">
                <button
                    v-for="item in currentItems"
                    :key="item.id"
                    @click="selectedItem = item"
                    class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 text-left hover:shadow-md transition-shadow"
                >
                    <div class="aspect-video bg-amber-100 flex items-center justify-center overflow-hidden">
                        <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-cover" />
                        <span v-else class="text-4xl">🍽️</span>
                    </div>
                    <div class="p-3">
                        <div class="font-semibold text-gray-900 text-sm leading-tight">{{ item.name }}</div>
                        <div v-if="item.description" class="text-xs text-gray-400 mt-0.5 truncate">{{ item.description }}</div>
                        <div class="text-amber-600 font-bold mt-1">${{ currency(item.price) }}</div>
                    </div>
                </button>
            </div>
        </template>

        <!-- Item detail modal -->
        <div v-if="selectedItem" class="fixed inset-0 bg-black/60 z-50 flex items-end sm:items-center justify-center p-4" @click.self="selectedItem = null">
            <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden">
                <div class="aspect-video bg-amber-100 flex items-center justify-center overflow-hidden">
                    <img v-if="selectedItem.image_path" :src="`/storage/${selectedItem.image_path}`" class="w-full h-full object-cover" />
                    <span v-else class="text-5xl">🍽️</span>
                </div>
                <div class="p-5 space-y-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ selectedItem.name }}</h2>
                        <p v-if="selectedItem.description" class="text-sm text-gray-500 mt-0.5">{{ selectedItem.description }}</p>
                        <div class="text-xl font-black text-amber-600 mt-1">${{ currency(selectedItem.price) }}</div>
                    </div>

                    <!-- Variants -->
                    <div v-if="selectedItem.variants?.length" class="space-y-2">
                        <div class="text-sm font-medium text-gray-700">Choose option:</div>
                        <button
                            v-for="v in selectedItem.variants"
                            :key="v.id"
                            @click="addItem(selectedItem, v)"
                            class="w-full flex items-center justify-between px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm hover:bg-amber-100"
                        >
                            <span class="font-medium text-gray-900">{{ v.name }}</span>
                            <span class="text-amber-600 font-semibold">+${{ currency(v.price_modifier) }}</span>
                        </button>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button @click="selectedItem = null" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-medium">Back</button>
                        <button v-if="!selectedItem.variants?.length" @click="addItem(selectedItem)" class="flex-1 bg-amber-500 text-white py-3 rounded-xl font-bold hover:bg-amber-600">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating cart button -->
        <div v-if="cart.length && !submitted" class="fixed bottom-6 inset-x-4 z-40">
            <button @click="showCart = true" class="w-full bg-amber-500 text-white py-4 rounded-2xl shadow-xl font-bold text-base flex items-center justify-between px-5 hover:bg-amber-600">
                <span class="bg-white text-amber-600 w-7 h-7 rounded-full flex items-center justify-center font-black text-sm">{{ cartCount }}</span>
                <span>View Order</span>
                <span>${{ currency(cartTotal) }}</span>
            </button>
        </div>

        <!-- Cart slide-up panel -->
        <div v-if="showCart" class="fixed inset-0 bg-black/50 z-50 flex items-end" @click.self="showCart = false">
            <div class="bg-white rounded-t-3xl w-full max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Your Order — Table {{ table.number }}</h2>
                    <button @click="showCart = false" class="text-gray-400 text-xl leading-none">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                    <div v-for="item in cart" :key="item.key" class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 text-sm">{{ item.name }}</div>
                            <div class="text-amber-600 text-sm font-semibold mt-0.5">${{ currency(item.unit_price) }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="removeOne(item.key)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xl font-light hover:bg-red-100 hover:text-red-600">−</button>
                            <span class="w-6 text-center font-bold text-gray-900">{{ item.quantity }}</span>
                            <button @click="addOne(item.key)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xl font-light hover:bg-green-100 hover:text-green-600">+</button>
                        </div>
                        <div class="w-16 text-right font-semibold text-gray-900">${{ currency(item.unit_price * item.quantity) }}</div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 space-y-3">
                    <textarea
                        v-model="orderNotes"
                        placeholder="Any special requests? (allergies, cooking preferences...)"
                        rows="2"
                        class="w-full text-sm border border-gray-200 rounded-xl px-4 py-2.5 resize-none focus:outline-none focus:ring-1 focus:ring-amber-400"
                    />
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-medium">Total</span>
                        <span class="text-xl font-black text-gray-900">${{ currency(cartTotal) }}</span>
                    </div>
                    <button
                        @click="placeOrder"
                        :disabled="form.processing"
                        class="w-full bg-amber-500 text-white py-4 rounded-2xl font-black text-lg hover:bg-amber-600 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Sending Order...' : '🍽️ Place Order' }}
                    </button>
                    <p class="text-xs text-center text-gray-400">Payment will be handled by a staff member at your table</p>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
