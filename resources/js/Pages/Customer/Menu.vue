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
        <div v-if="submitted" class="flex flex-col items-center justify-center min-h-[70vh] text-center px-8 py-12 space-y-6">
            <div class="relative">
                <div class="absolute inset-0 bg-emerald-500 blur-2xl opacity-20 animate-pulse"></div>
                <div class="relative w-24 h-24 bg-emerald-500 text-white rounded-full flex items-center justify-center text-5xl shadow-2xl shadow-emerald-500/20">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
            <div class="space-y-2">
                <h1 class="text-3xl font-black tracking-tighter text-[var(--text-strong)]">{{ __('Order Received!') }}</h1>
                <p class="text-[var(--text-muted)] leading-relaxed">{{ __('Your selection has been sent to our master chefs. Relax and enjoy — we\'ll take it from here.') }}</p>
            </div>
            <button @click="submitted = false" class="w-full bg-[var(--brand)] text-white py-4 rounded-2xl font-black text-lg hover:bg-[var(--brand-hover)] shadow-xl shadow-[var(--brand-glow)] transition-all">{{ __('Order More') }}</button>
        </div>

        <template v-else>
            <!-- Category navigation (Sticky) -->
            <div class="sticky top-[72px] z-40 bg-[var(--bg-main)]/95 backdrop-blur-md -mx-4 px-4 py-4 border-b border-[var(--border)] overflow-x-auto scrollbar-none flex gap-3">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCategory = cat.id"
                    class="shrink-0 px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all"
                    :class="selectedCategory === cat.id 
                        ? 'bg-[var(--text-strong)] text-[var(--bg-main)] shadow-lg' 
                        : 'bg-[var(--bg-card)] text-[var(--text-muted)] border border-[var(--border)]'"
                >{{ cat.name }}</button>
            </div>

            <!-- Items list (Card style) -->
            <div class="mt-6 space-y-4">
                <div 
                    v-for="item in currentItems"
                    :key="item.id"
                    @click="selectedItem = item"
                    class="bg-[var(--bg-card)] border border-[var(--border)] rounded-2xl overflow-hidden flex items-stretch h-32 active:scale-[0.98] transition-all hover:border-[var(--brand)] group"
                >
                    <div class="w-32 bg-[var(--bg-surface)] relative overflow-hidden">
                        <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                        <div v-else class="w-full h-full flex items-center justify-center text-3xl opacity-30 grayscale group-hover:grayscale-0 transition-all">🍽️</div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    
                    <div class="flex-1 p-4 flex flex-col justify-between min-w-0">
                        <div class="min-w-0">
                            <h3 class="font-black text-[var(--text-strong)] leading-none truncate">{{ item.name }}</h3>
                            <p v-if="item.description" class="text-[10px] text-[var(--text-muted)] mt-1.5 line-clamp-2 leading-relaxed uppercase tracking-tight font-medium">{{ item.description }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-lg font-black text-[var(--brand)]">${{ currency(item.price) }}</span>
                            <div class="w-8 h-8 rounded-full bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-[var(--text-strong)] group-hover:bg-[var(--brand)] group-hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Item Detail Board (Bottom Sheet style) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="translate-y-full" enter-to-class="translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="translate-y-0" leave-to-class="translate-y-full">
            <div v-if="selectedItem" class="fixed inset-0 z-50 flex items-end">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="selectedItem = null"></div>
                <div class="relative w-full bg-[var(--bg-card)] rounded-t-[40px] px-6 pb-12 pt-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                    <!-- Pull bar -->
                    <div class="w-12 h-1.5 bg-[var(--border)] rounded-full mx-auto mb-6"></div>
                    
                    <div class="aspect-video -mx-6 -mt-4 bg-[var(--bg-surface)] relative overflow-hidden mb-6">
                        <img v-if="selectedItem.image_path" :src="`/storage/${selectedItem.image_path}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-7xl opacity-20">🍽️</div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h2 class="text-3xl font-black tracking-tighter text-[var(--text-strong)]">{{ selectedItem.name }}</h2>
                            <p v-if="selectedItem.description" class="text-base text-[var(--text-muted)] mt-2 italic">{{ selectedItem.description }}</p>
                            <div class="text-2xl font-black text-[var(--brand)] mt-4">${{ currency(selectedItem.price) }}</div>
                        </div>

                        <!-- Variants -->
                        <div v-if="selectedItem.variants?.length" class="space-y-3">
                            <div class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">{{ __('Customizations') }}</div>
                            <div class="grid grid-cols-1 gap-2">
                                <button
                                    v-for="v in selectedItem.variants"
                                    :key="v.id"
                                    @click="addItem(selectedItem, v)"
                                    class="w-full flex items-center justify-between px-6 py-4 bg-[var(--bg-surface)] border border-[var(--border)] rounded-2xl hover:border-[var(--brand)] text-left group"
                                >
                                    <span class="font-bold text-[var(--text-strong)] text-sm">{{ v.name }}</span>
                                    <span class="text-[var(--brand)] font-black">+${{ currency(v.price_modifier) }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button @click="selectedItem = null" class="flex-1 bg-[var(--bg-surface)] text-[var(--text-strong)] py-4 rounded-2xl font-bold border border-[var(--border)] active:scale-95 transition-all">{{ __('Back') }}</button>
                            <button v-if="!selectedItem.variants?.length" @click="addItem(selectedItem)" class="flex-[2] bg-[var(--brand)] text-white py-4 rounded-2xl font-black text-lg shadow-xl shadow-[var(--brand-glow)] active:scale-95 transition-all">{{ __('Add To Bag') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Dynamic Cart Toggle -->
        <div v-if="cart.length && !submitted" class="fixed bottom-8 inset-x-6 z-40">
            <button @click="showCart = true" class="w-full bg-[var(--text-strong)] text-[var(--bg-main)] py-5 rounded-3xl shadow-2xl flex items-center justify-between px-8 group active:scale-[0.98] transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-black text-sm shadow-lg shadow-[var(--brand-glow)] group-active:scale-110 transition-transform">
                        {{ cartCount }}
                    </div>
                    <span class="text-sm font-black uppercase tracking-widest">{{ __('Review Selection') }}</span>
                </div>
                <span class="text-xl font-black italic">${{ currency(cartTotal) }}</span>
            </button>
        </div>

        <!-- Cart Panel (Full Screen) -->
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-12" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-12">
            <div v-if="showCart" class="fixed inset-0 z-50 bg-[var(--bg-main)] flex flex-col">
                <div class="px-6 py-8 border-b border-[var(--border)] flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black tracking-tighter text-[var(--text-strong)] uppercase">Your Cravings</h2>
                        <p class="text-xs font-bold text-[var(--text-muted)] tracking-widest uppercase">Table {{ table.number }} • {{ cartCount }} Items</p>
                    </div>
                    <button @click="showCart = false" class="w-12 h-12 rounded-full border border-[var(--border)] flex items-center justify-center text-2xl text-[var(--text-muted)] hover:text-[var(--text-strong)]">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    <div v-for="item in cart" :key="item.key" class="flex items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="font-black text-[var(--text-strong)]">{{ item.name }}</div>
                            <div class="text-[var(--brand)] text-xs font-black mt-1 uppercase italic">${{ currency(item.unit_price) }}</div>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex items-center bg-[var(--bg-surface)] border border-[var(--border)] rounded-full px-2 py-1">
                                <button @click="removeOne(item.key)" class="w-8 h-8 rounded-full hover:bg-red-500 hover:text-white flex items-center justify-center text-xl font-light transition-colors">−</button>
                                <span class="w-8 text-center font-black text-[var(--text-strong)]">{{ item.quantity }}</span>
                                <button @click="addOne(item.key)" class="w-8 h-8 rounded-full hover:bg-emerald-500 hover:text-white flex items-center justify-center text-xl font-light transition-colors">+</button>
                            </div>
                            <div class="text-[10px] font-black text-[var(--text-strong)]">${{ currency(item.unit_price * item.quantity) }}</div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-8 border-t border-[var(--border)] bg-[var(--bg-card)] space-y-6">
                    <textarea
                        v-model="orderNotes"
                        placeholder="ALLEGIES OR SPECIAL REQUESTS?"
                        rows="2"
                        class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] text-xs font-bold rounded-2xl px-5 py-4 resize-none focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none uppercase placeholder:text-[var(--text-muted)]"
                    />
                    <div class="flex items-center justify-between">
                        <span class="text-[var(--text-muted)] text-sm font-black uppercase tracking-widest">Total Valuation</span>
                        <span class="text-4xl font-black italic text-[var(--text-strong)] tracking-tighter">${{ currency(cartTotal) }}</span>
                    </div>
                    <button
                        @click="placeOrder"
                        :disabled="form.processing"
                        class="w-full bg-[var(--brand)] text-white py-6 rounded-3xl font-black text-2xl uppercase tracking-tighter hover:bg-[var(--brand-hover)] shadow-2xl shadow-[var(--brand-glow)] disabled:opacity-50 transition-all active:scale-[0.98]"
                    >
                        {{ form.processing ? 'Transmitting...' : 'Confirm Order' }}
                    </button>
                    <p class="text-[10px] text-center text-[var(--text-muted)] font-bold uppercase tracking-widest opacity-50">
                        Proceed To Confirmation • Secure Connection
                    </p>
                </div>
            </div>
        </Transition>
    </CustomerLayout>
</template>
