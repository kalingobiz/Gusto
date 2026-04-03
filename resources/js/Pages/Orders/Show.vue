<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({ order: Object });
const order = ref(props.order);

// Keep order ref in sync whenever Inertia refreshes the page props
watch(() => props.order, (updated) => {
    order.value = updated;
    if (!showPayment.value) {
        payForm.amount = updated.total;
    }
}, { deep: true });

// --- Status badge styles ---
const kitchenStatusConfig = {
    pending:     { label: 'Pending',     cls: 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)]' },
    in_progress: { label: 'Cooking',     cls: 'bg-amber-500/10 text-amber-500 border-amber-500/20' },
    done:        { label: 'Done',        cls: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
    voided:      { label: 'Voided',      cls: 'bg-[var(--danger)]/10 text-[var(--danger)] border-[var(--danger)]/20' },
};

const orderStatusConfig = {
    draft:       { label: 'Draft',       cls: 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)]' },
    confirmed:   { label: 'Confirmed',   cls: 'bg-blue-500/10 text-blue-400 border-blue-500/20' },
    in_progress: { label: 'In Progress', cls: 'bg-amber-500/10 text-amber-400 border-amber-500/20' },
    ready:       { label: 'Ready',       cls: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' },
    served:      { label: 'Served',      cls: 'bg-purple-500/10 text-purple-400 border-purple-500/20' },
    paid:        { label: 'Paid',        cls: 'bg-emerald-500 text-white border-emerald-600' },
    voided:      { label: 'Voided',      cls: 'bg-[var(--danger)]/10 text-[var(--danger)] border-[var(--danger)]/20' },
};

function currency(v) { return Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

// Void item
const voidForm  = useForm({ reason: '' });
const voidingItem = ref(null);
function startVoid(item) { voidingItem.value = item; voidForm.reason = ''; }
function confirmVoid() {
    voidForm.post(route('orders.void-item', [order.value.id, voidingItem.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            voidingItem.value = null;
            router.reload({ only: ['order'] });
        },
    });
}

// Payment
const payForm = useForm({
    order_id: order.value.id,
    method:   'cash',
    amount:   order.value.total,
    reference: '',
    bank_name: '',
});
const showPayment = ref(false);
function submitPayment() {
    payForm.post(route('payments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showPayment.value = false;
            router.reload({ only: ['order'] });
        },
    });
}

// Real-time
let echoChannel;
onMounted(() => {
    if (window.Echo) {
        echoChannel = window.Echo.channel(`orders.${order.value.id}`)
            .listen('.order.status-changed', (data) => {
                order.value.status = data.status;
                order.value.total  = data.total;
            })
            .listen('.order-item.status-changed', (data) => {
                const item = order.value.items?.find(i => i.id === data.item.id);
                if (item) item.kitchen_status = data.item.kitchen_status;
            });
    }
});
onUnmounted(() => echoChannel?.stopListening('.order.status-changed'));

const activeItems  = computed(() => order.value.items?.filter(i => i.kitchen_status !== 'voided') ?? []);
const voidedItems  = computed(() => order.value.items?.filter(i => i.kitchen_status === 'voided') ?? []);
const progressPct  = computed(() => {
    const a = activeItems.value;
    if (!a.length) return 100;
    const done = a.filter(i => i.kitchen_status === 'done').length;
    return Math.round((done / a.length) * 100);
});
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">

            <!-- ── Page Header ─────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-3xl font-heading font-black tracking-tight text-[var(--text-strong)]">
                            Order <span class="text-[var(--brand)]">#{{ order.id }}</span>
                        </h1>
                        <!-- Live status badge -->
                        <span
                            class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest border transition-all"
                            :class="orderStatusConfig[order.status]?.cls"
                        >{{ orderStatusConfig[order.status]?.label }}</span>
                    </div>
                    <p class="text-sm text-[var(--text-muted)] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Table {{ order.restaurant_table?.number }}
                        <span class="text-[var(--border)]">·</span>
                        <span>{{ order.source === 'customer_qr' ? '📱 QR Order' : '🖥️ Staff Order' }}</span>
                    </p>
                </div>

                <!-- Kitchen Progress -->
                <div v-if="!['paid','voided'].includes(order.status)" class="glass-panel px-5 py-4 min-w-[180px]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Kitchen Progress</span>
                        <span class="text-sm font-black text-[var(--text-strong)]">{{ progressPct }}%</span>
                    </div>
                    <div class="h-2 bg-[var(--bg-surface)] rounded-full overflow-hidden border border-[var(--border)]">
                        <div
                            class="h-full bg-emerald-500 rounded-full transition-all duration-700"
                            :style="{ width: progressPct + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- ── Order Items ──────────────────────────────────── -->
            <div class="glass-card overflow-hidden">
                <div class="px-6 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)] flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Order Items</h2>
                    <span class="text-xs text-[var(--text-muted)] font-bold">{{ activeItems.length }} item{{ activeItems.length !== 1 ? 's' : '' }}</span>
                </div>

                <!-- Active items -->
                <div class="divide-y divide-[var(--border)]">
                    <div
                        v-for="item in activeItems"
                        :key="item.id"
                        class="px-6 py-4 flex items-center gap-4 hover:bg-[var(--bg-surface)]/50 transition-colors group"
                    >
                        <!-- Item icon -->
                        <div class="w-10 h-10 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-base flex-shrink-0">
                            🍽️
                        </div>

                        <!-- Name + notes -->
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-[var(--text-strong)] truncate">
                                {{ item.menu_item?.name }}
                                <span v-if="item.variant" class="font-normal text-[var(--text-muted)] text-sm"> · {{ item.variant.name }}</span>
                            </div>
                            <div v-if="item.notes" class="text-xs text-[var(--text-muted)] mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                {{ item.notes }}
                            </div>
                        </div>

                        <!-- Qty -->
                        <div class="text-sm font-black text-[var(--text-muted)] w-10 text-center">
                            ×{{ item.quantity }}
                        </div>

                        <!-- Line total -->
                        <div class="text-sm font-black text-[var(--text-strong)] w-20 text-right">
                            ${{ currency(item.line_total) }}
                        </div>

                        <!-- Kitchen badge -->
                        <div class="w-24 flex justify-center">
                            <span
                                class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border"
                                :class="kitchenStatusConfig[item.kitchen_status]?.cls"
                            >{{ kitchenStatusConfig[item.kitchen_status]?.label }}</span>
                        </div>

                        <!-- Void btn -->
                        <button
                            v-if="!['voided','done'].includes(item.kitchen_status) && !['paid','voided'].includes(order.status)"
                            @click="startVoid(item)"
                            class="opacity-0 group-hover:opacity-100 p-2 rounded-lg text-[var(--danger)] hover:bg-[var(--danger)]/10 transition-all"
                            title="Void item"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        <div v-else class="w-8"></div>
                    </div>

                    <!-- Empty -->
                    <div v-if="!activeItems.length" class="py-16 text-center text-[var(--text-muted)] italic text-sm">
                        No active items on this order.
                    </div>
                </div>

                <!-- Voided items collapsible -->
                <details v-if="voidedItems.length" class="border-t border-[var(--border)]">
                    <summary class="px-6 py-3 text-xs font-black uppercase tracking-widest text-[var(--danger)]/60 cursor-pointer hover:bg-[var(--danger)]/5 select-none transition-colors list-none flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ voidedItems.length }} Voided Item{{ voidedItems.length > 1 ? 's' : '' }}
                    </summary>
                    <div class="divide-y divide-[var(--border)]/50">
                        <div
                            v-for="item in voidedItems"
                            :key="item.id"
                            class="px-6 py-3 flex items-center gap-4 opacity-50"
                        >
                            <div class="w-10 h-10 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex items-center justify-center text-base">🍽️</div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-[var(--text-muted)] line-through truncate">{{ item.menu_item?.name }}</div>
                                <div v-if="item.void_log" class="text-[10px] text-[var(--danger)] font-bold">{{ item.void_log.reason }}</div>
                            </div>
                            <div class="text-sm text-[var(--text-muted)] line-through">${{ currency(item.line_total) }}</div>
                        </div>
                    </div>
                </details>

                <!-- Total row -->
                <div class="px-6 py-5 bg-[var(--bg-surface)] border-t border-[var(--border)] flex items-center justify-between">
                    <span class="text-sm font-black uppercase tracking-widest text-[var(--text-muted)]">Total</span>
                    <span class="text-2xl font-heading font-black text-[var(--text-strong)]">${{ currency(order.total) }}</span>
                </div>
            </div>

            <!-- ── Payment Confirmed Banner ─────────────────────── -->
            <div v-if="order.payment" class="glass-card border-2 border-emerald-500/20 bg-emerald-500/5 px-6 py-5 flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div class="text-sm font-black text-emerald-500 uppercase tracking-widest mb-0.5">Payment Confirmed</div>
                    <div class="text-[var(--text-muted)] text-sm font-medium">
                        {{ order.payment.method === 'cash' ? 'Cash' : 'Bank Transfer' }} ·
                        ${{ currency(order.payment.amount) }}
                        <span v-if="order.payment.reference"> · Ref: {{ order.payment.reference }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Action Buttons ──────────────────────────────── -->
            <div v-if="!['paid', 'voided'].includes(order.status)" class="flex flex-col sm:flex-row gap-3">
                <button
                    v-if="order.status === 'ready'"
                    @click="router.patch(route('orders.served', order.id))"
                    class="flex-1 btn-primary py-3.5 text-sm flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #a855f7, #7c3aed);"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mark as Served
                </button>
                <button
                    v-if="['served','ready','confirmed','in_progress'].includes(order.status)"
                    @click="showPayment = true"
                    class="flex-1 btn-primary py-3.5 text-sm flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, #10b981, #059669);"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Record Payment
                </button>
            </div>

        </div>

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- PAYMENT MODAL                                          -->
        <!-- ══════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showPayment" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPayment = false"></div>

                    <!-- Sheet -->
                    <div class="relative w-full max-w-md glass-panel shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-surface)] flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-heading font-black text-[var(--text-strong)]">Record Payment</h2>
                                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Order #{{ order.id }} · ${{ currency(order.total) }}</p>
                            </div>
                            <button @click="showPayment = false" class="btn-icon w-9 h-9">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Validation errors -->
                        <div v-if="Object.keys(payForm.errors).length" class="mx-6 mt-4 px-4 py-3 rounded-xl bg-[var(--danger)]/10 border border-[var(--danger)]/30 text-[var(--danger)] text-xs font-bold space-y-1">
                            <p v-for="(msg, field) in payForm.errors" :key="field">{{ msg }}</p>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <!-- Amount -->
                            <div>
                                <label class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Amount</label>
                                <input
                                    v-model="payForm.amount"
                                    type="number"
                                    step="0.01"
                                    class="input-premium text-2xl font-black"
                                    :class="payForm.errors.amount ? 'ring-2 ring-[var(--danger)]' : ''"
                                />
                            </div>

                            <!-- Method toggle -->
                            <div>
                                <label class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Method</label>
                                <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)]">
                                    <button
                                        v-for="m in [{v:'cash', icon:'💵', label:'Cash'}, {v:'bank_transfer', icon:'🏦', label:'Transfer'}]"
                                        :key="m.v"
                                        @click="payForm.method = m.v"
                                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-black transition-all"
                                        :class="payForm.method === m.v
                                            ? 'bg-[var(--brand)] text-white shadow-lg shadow-[var(--brand-glow)]'
                                            : 'text-[var(--text-muted)] hover:text-[var(--text-strong)]'"
                                    >
                                        <span>{{ m.icon }}</span> {{ m.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Bank fields -->
                            <div v-if="payForm.method === 'bank_transfer'" class="space-y-3">
                                <div>
                                    <label class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Bank Name</label>
                                    <input v-model="payForm.bank_name" type="text" class="input-premium" placeholder="e.g. Chase, Barclays..." />
                                </div>
                                <div>
                                    <label class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Reference #</label>
                                    <input v-model="payForm.reference" type="text" class="input-premium" placeholder="Transaction reference" />
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-6 pb-6 flex gap-3">
                            <button @click="showPayment = false" class="btn-secondary flex-1 py-3">Cancel</button>
                            <button
                                @click="submitPayment"
                                :disabled="payForm.processing"
                                class="btn-primary flex-1 py-3 disabled:opacity-50 flex items-center justify-center gap-2"
                                style="background: linear-gradient(135deg, #10b981, #059669);"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ payForm.processing ? 'Processing...' : 'Confirm Payment' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- VOID MODAL                                             -->
        <!-- ══════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="voidingItem" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="voidingItem = null"></div>

                    <div class="relative w-full max-w-md glass-panel shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-[var(--danger)]/20 bg-[var(--danger)]/5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[var(--danger)]/10 border border-[var(--danger)]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[var(--danger)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-heading font-black text-[var(--danger)]">Void Item</h2>
                                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">{{ voidingItem.menu_item?.name }}</p>
                            </div>
                        </div>

                        <div class="px-6 py-6 space-y-4">
                            <p class="text-sm text-[var(--text-muted)]">This action cannot be undone. Please provide a reason.</p>
                            <div>
                                <label class="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] block mb-2">Reason <span class="text-[var(--danger)]">*</span></label>
                                <input
                                    v-model="voidForm.reason"
                                    type="text"
                                    class="input-premium focus:ring-[var(--danger)] focus:border-transparent"
                                    placeholder="e.g. Wrong item ordered, customer changed mind..."
                                    @keyup.enter="confirmVoid"
                                />
                                <div v-if="voidForm.errors.reason" class="mt-1.5 text-xs font-bold text-[var(--danger)]">{{ voidForm.errors.reason }}</div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex gap-3">
                            <button @click="voidingItem = null" class="btn-secondary flex-1 py-3">Cancel</button>
                            <button
                                @click="confirmVoid"
                                :disabled="!voidForm.reason || voidForm.processing"
                                class="btn-danger flex-1 py-3 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                {{ voidForm.processing ? 'Voiding...' : 'Void Item' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 200ms ease, transform 200ms ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: translateY(16px);
}
</style>
