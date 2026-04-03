<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    table:      Object,
    categories: Array,
});

const cart             = ref([]);
const selectedCategory = ref(props.categories?.[0]?.id);
const notes            = ref('');
const expandedItem     = ref(null); // for inline notes per item

const cartTotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.unit_price * item.quantity, 0)
);

const currentItems = computed(() =>
    props.categories?.find(c => c.id === selectedCategory.value)?.menu_items?.filter(i => i.is_available) ?? []
);

const cartCount = computed(() => cart.value.reduce((s, i) => s + i.quantity, 0));

function addItem(menuItem, variant = null) {
    const key = `${menuItem.id}-${variant?.id ?? 0}`;
    const existing = cart.value.find(c => c.key === key);
    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            key,
            menu_item_id: menuItem.id,
            variant_id:   variant?.id ?? null,
            name:  menuItem.name + (variant ? ` · ${variant.name}` : ''),
            unit_price: parseFloat(menuItem.price) + (variant ? parseFloat(variant.price_modifier) : 0),
            quantity: 1,
            notes: '',
            image_path: menuItem.image_path ?? null,
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

function deleteCartItem(key) {
    cart.value = cart.value.filter(c => c.key !== key);
}

const form = useForm({});
function placeOrder() {
    form.transform(() => ({
        table_token: props.table.token,
        notes: notes.value,
        items: cart.value.map(item => ({
            menu_item_id: item.menu_item_id,
            variant_id:   item.variant_id,
            quantity:     item.quantity,
            notes:        item.notes,
        })),
    })).post(route('orders.store'), { preserveScroll: true });
}

function currency(v) { return Number(v).toFixed(2); }

function getCartQty(menuItemId, variantId = null) {
    const key = `${menuItemId}-${variantId ?? 0}`;
    return cart.value.find(c => c.key === key)?.quantity ?? 0;
}
</script>

<template>
    <AppLayout>
        <div class="flex gap-0 -m-6 min-h-[calc(100vh-80px)]">

            <!-- ══════════════════════════════════════════════════ -->
            <!-- LEFT: Menu Panel                                   -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="flex-1 flex flex-col min-w-0 border-r border-[var(--border)]">

                <!-- Header bar -->
                <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-card)] flex items-center justify-between flex-shrink-0">
                    <div>
                        <h1 class="text-xl font-heading font-black tracking-tight text-[var(--text-strong)] flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-[var(--brand)]/10 border border-[var(--brand)]/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-[var(--brand)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            New Order
                        </h1>
                        <p class="text-xs text-[var(--text-muted)] font-bold mt-0.5">
                            Table <span class="text-[var(--brand)]">{{ table.number }}</span>
                            <span class="mx-1.5 text-[var(--border)]">·</span>
                            {{ table.capacity }} seats
                        </p>
                    </div>
                </div>

                <!-- Category tabs – scrollable pill strip -->
                <div class="px-6 py-3 border-b border-[var(--border)] bg-[var(--bg-card)] flex gap-2 overflow-x-auto flex-shrink-0"
                     style="scrollbar-width: none;">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="shrink-0 px-5 py-2 rounded-full text-sm font-black uppercase tracking-wider transition-all border btn-haptic"
                        :class="selectedCategory === cat.id
                            ? 'bg-[var(--brand)] text-white border-transparent shadow-lg shadow-[var(--brand-glow)]'
                            : 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)] hover:border-[var(--brand)] hover:text-[var(--text-strong)]'"
                    >
                        {{ cat.name }}
                        <span
                            class="ml-1.5 text-[10px]"
                            :class="selectedCategory === cat.id ? 'text-white/70' : 'text-[var(--text-muted)]'"
                        >({{ cat.menu_items?.filter(i => i.is_available).length ?? 0 }})</span>
                    </button>
                </div>

                <!-- Items grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div
                        v-if="currentItems.length"
                        class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <button
                            v-for="item in currentItems"
                            :key="item.id"
                            @click="addItem(item)"
                            class="relative glass-card text-left p-0 overflow-hidden btn-haptic group transition-all duration-200 hover:-translate-y-0.5"
                        >
                            <!-- Quantity badge -->
                            <div
                                v-if="getCartQty(item.id) > 0"
                                class="absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-[var(--brand)] text-white text-xs font-black flex items-center justify-center shadow-lg"
                            >
                                {{ getCartQty(item.id) }}
                            </div>

                            <!-- Image or placeholder -->
                            <div class="w-full aspect-[4/3] bg-[var(--bg-surface)] overflow-hidden">
                                <img
                                    v-if="item.image_path"
                                    :src="`/storage/${item.image_path}`"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    :alt="item.name"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-4xl bg-gradient-to-br from-[var(--bg-surface)] to-[var(--bg-main)]">
                                    🍽️
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-3">
                                <div class="font-bold text-sm text-[var(--text-strong)] leading-tight mb-1 truncate">{{ item.name }}</div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[var(--brand)] font-black text-sm">${{ currency(item.price) }}</span>
                                    <!-- plus indicator -->
                                    <div class="w-6 h-6 rounded-lg bg-[var(--brand)]/10 border border-[var(--brand)]/20 flex items-center justify-center group-hover:bg-[var(--brand)] group-hover:border-[var(--brand)] transition-all">
                                        <svg class="w-3.5 h-3.5 text-[var(--brand)] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v14M5 12h14"/></svg>
                                    </div>
                                </div>

                                <!-- Variants (inline, smaller buttons) -->
                                <div v-if="item.variants?.length" class="mt-2 pt-2 border-t border-[var(--border)] space-y-1">
                                    <button
                                        v-for="v in item.variants"
                                        :key="v.id"
                                        @click.stop="addItem(item, v)"
                                        class="w-full text-xs text-left px-2 py-1.5 rounded-lg font-bold transition-all flex items-center justify-between gap-1"
                                        :class="getCartQty(item.id, v.id) > 0
                                            ? 'bg-[var(--brand)]/10 text-[var(--brand)] border border-[var(--brand)]/20'
                                            : 'bg-[var(--bg-surface)] text-[var(--text-muted)] hover:bg-[var(--bg-main)] border border-transparent'"
                                    >
                                        <span class="truncate">{{ v.name }}</span>
                                        <span class="flex-shrink-0 text-[10px] font-black">+${{ currency(v.price_modifier) }}</span>
                                    </button>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Empty category -->
                    <div v-else class="flex flex-col items-center justify-center h-64 gap-4 text-[var(--text-muted)]">
                        <div class="w-16 h-16 rounded-2xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-3xl">🍽️</div>
                        <p class="text-sm font-bold">No available items in this category</p>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- RIGHT: Cart Panel                                  -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="w-80 xl:w-96 flex-shrink-0 flex flex-col bg-[var(--bg-card)]">

                <!-- Cart header -->
                <div class="px-6 py-5 border-b border-[var(--border)] flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <svg class="w-5 h-5 text-[var(--text-strong)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <div
                                v-if="cartCount > 0"
                                class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full bg-[var(--brand)] text-white text-[9px] font-black flex items-center justify-center"
                            >{{ cartCount }}</div>
                        </div>
                        <h2 class="text-sm font-heading font-black text-[var(--text-strong)]">Order Cart</h2>
                    </div>
                    <button
                        v-if="cart.length"
                        @click="cart = []"
                        class="text-[10px] font-black uppercase tracking-widest text-[var(--danger)] hover:text-white hover:bg-[var(--danger)] px-2.5 py-1 rounded-lg transition-all border border-[var(--danger)]/20"
                    >
                        Clear
                    </button>
                </div>

                <!-- Cart items -->
                <div class="flex-1 overflow-y-auto divide-y divide-[var(--border)]">
                    <TransitionGroup name="cart-item" tag="div">
                        <div
                            v-for="item in cart"
                            :key="item.key"
                            class="px-5 py-4"
                        >
                            <div class="flex items-start gap-3">
                                <!-- Mini image -->
                                <div class="w-10 h-10 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-base flex-shrink-0 overflow-hidden">
                                    <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-full h-full object-cover" />
                                    <span v-else>🍽️</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-[var(--text-strong)] leading-tight truncate">{{ item.name }}</div>
                                    <div class="text-xs text-[var(--text-muted)] font-medium mt-0.5">${{ currency(item.unit_price) }} each</div>
                                </div>

                                <!-- Delete -->
                                <button
                                    @click="deleteCartItem(item.key)"
                                    class="w-6 h-6 rounded-lg hover:bg-[var(--danger)]/10 text-[var(--text-muted)] hover:text-[var(--danger)] flex items-center justify-center transition-colors flex-shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Qty + line total row -->
                            <div class="flex items-center justify-between mt-3">
                                <!-- Qty controls -->
                                <div class="flex items-center gap-2 bg-[var(--bg-surface)] border border-[var(--border)] rounded-xl p-1">
                                    <button
                                        @click="removeItem(item.key)"
                                        class="w-7 h-7 rounded-lg bg-[var(--bg-card)] border border-[var(--border)] text-[var(--text-strong)] font-black flex items-center justify-center hover:border-[var(--brand)] hover:text-[var(--brand)] transition-all btn-haptic"
                                    >−</button>
                                    <span class="w-6 text-center font-black text-sm text-[var(--text-strong)]">{{ item.quantity }}</span>
                                    <button
                                        @click="addItem({id: item.menu_item_id, price: item.unit_price, variants: []}, item.variant_id ? {id: item.variant_id, price_modifier: 0} : null)"
                                        class="w-7 h-7 rounded-lg bg-[var(--brand)] text-white font-black flex items-center justify-center hover:bg-[var(--brand-hover)] transition-all btn-haptic"
                                    >+</button>
                                </div>

                                <span class="text-sm font-black text-[var(--text-strong)]">
                                    ${{ currency(item.unit_price * item.quantity) }}
                                </span>
                            </div>

                            <!-- Per-item notes toggle -->
                            <button
                                @click="expandedItem = expandedItem === item.key ? null : item.key"
                                class="mt-2 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--brand)] transition-colors flex items-center gap-1"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                {{ item.notes ? 'Edit note' : '+ Add note' }}
                            </button>
                            <Transition name="slide-down">
                                <div v-if="expandedItem === item.key" class="mt-2">
                                    <input
                                        v-model="item.notes"
                                        type="text"
                                        class="input-premium text-xs py-2"
                                        placeholder="e.g. No onions, extra sauce..."
                                    />
                                </div>
                            </Transition>
                        </div>
                    </TransitionGroup>

                    <!-- Empty cart state -->
                    <div v-if="!cart.length" class="flex flex-col items-center justify-center h-64 gap-4 text-[var(--text-muted)]">
                        <div class="w-16 h-16 rounded-2xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center">
                            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <p class="text-sm font-bold">Tap items to add them</p>
                        <p class="text-xs text-center max-w-[160px]">Your order will appear here</p>
                    </div>
                </div>

                <!-- Cart footer: notes + total + place order -->
                <div class="border-t border-[var(--border)] p-5 space-y-4 flex-shrink-0">

                    <!-- Order notes -->
                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Order Notes</label>
                        <textarea
                            v-model="notes"
                            rows="2"
                            class="input-premium resize-none text-sm"
                            placeholder="Allergies, special requests..."
                        ></textarea>
                    </div>

                    <!-- Total -->
                    <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)]">
                        <span class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">Order Total</span>
                        <span class="text-xl font-heading font-black text-[var(--text-strong)]">${{ currency(cartTotal) }}</span>
                    </div>

                    <!-- Place order CTA -->
                    <button
                        @click="placeOrder"
                        :disabled="!cart.length || form.processing"
                        class="w-full btn-primary py-4 text-base disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-3"
                        style="background: linear-gradient(135deg, var(--brand) 0%, var(--brand-hover) 100%);"
                    >
                        <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <!-- Spinner -->
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>

                        <span class="font-black tracking-wide">
                            {{ form.processing ? 'Placing Order...' : 'Place Order' }}
                        </span>

                        <span v-if="!form.processing && cartCount > 0" class="ml-auto text-white/70 text-sm font-bold">
                            {{ cartCount }} item{{ cartCount !== 1 ? 's' : '' }}
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Cart item entrance/exit animation */
.cart-item-enter-active { transition: all 250ms ease; }
.cart-item-leave-active { transition: all 200ms ease; }
.cart-item-enter-from   { opacity: 0; transform: translateX(-12px); }
.cart-item-leave-to     { opacity: 0; transform: translateX(12px) scale(0.95); }

/* Note field slide */
.slide-down-enter-active { transition: all 200ms ease; max-height: 80px; }
.slide-down-leave-active { transition: all 180ms ease; max-height: 80px; }
.slide-down-enter-from,
.slide-down-leave-to     { opacity: 0; max-height: 0; overflow: hidden; }
</style>
