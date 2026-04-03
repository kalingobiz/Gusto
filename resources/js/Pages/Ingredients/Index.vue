<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import { useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    ingredients: Object, // Changed to Object for pagination
    filters: Object
});

const searchTerm = ref(props.filters.search || '');
const isLowStockFilter = computed(() => props.filters.filter === 'low_stock');

function toggleLowStockFilter() {
    router.get(route('admin.ingredients.index'), {
        search: searchTerm.value || undefined,
        filter: isLowStockFilter.value ? undefined : 'low_stock',
    }, { preserveState: true, replace: true });
}

const showCreateForm = ref(false);
const showIntakeForm = ref(false);
const intakeIngredient = ref(null);

const createForm = useForm({
    name: '', unit: '', current_stock: 0, reorder_level: 0, cost_per_unit: 0,
});
function createIngredient() {
    createForm.post(route('admin.ingredients.store'), {
        preserveScroll: true,
        onSuccess: () => { createForm.reset(); showCreateForm.value = false; },
    });
}

const intakeForm = useForm({
    ingredient_id: null,
    quantity: '',
    cost_per_unit: '',
    supplier: '',
    reference: '',
    intake_date: new Date().toISOString().split('T')[0],
    notes: '',
});
function openIntake(ingredient) {
    intakeIngredient.value = ingredient;
    intakeForm.ingredient_id = ingredient.id;
    intakeForm.cost_per_unit = ingredient.cost_per_unit;
    showIntakeForm.value = true;
}
function submitIntake() {
    intakeForm.post(route('admin.ingredients.intake'), {
        preserveScroll: true,
        onSuccess: () => { showIntakeForm.value = false; intakeForm.reset(); },
    });
}

const showAdjustForm = ref(false);
const adjustIngredient = ref(null);
const adjustForm = useForm({
    ingredient_id: null,
    type: 'spoilage',
    quantity: '',
    notes: '',
});
function openAdjust(ingredient) {
    adjustIngredient.value = ingredient;
    adjustForm.ingredient_id = ingredient.id;
    showAdjustForm.value = true;
}
function submitAdjust() {
    adjustForm.post(route('admin.ingredients.adjust'), {
        preserveScroll: true,
        onSuccess: () => { showAdjustForm.value = false; adjustForm.reset(); },
    });
}

const deleteForm = useForm({});
function deleteIngredient(ingredient) {
    if (confirm(`Delete "${ingredient.name}"?`)) {
        deleteForm.delete(route('admin.ingredients.destroy', ingredient.id), { preserveScroll: true });
    }
}

function stockClass(ing) {
    return ing.is_low_stock
        ? 'text-[var(--danger)] font-black'
        : 'text-[var(--text-strong)]';
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">Inventory & Stock</h1>
                    <p class="text-[var(--text-muted)] text-sm">Monitor and manage your raw material stock levels.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.stocktakes.index')" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--bg-surface)] text-[var(--text-strong)] border border-[var(--border)] rounded-2xl hover:bg-[var(--bg-card)] font-bold transition-all active:scale-95">
                        Stock Audits
                    </Link>
                    <button @click="showCreateForm = true" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--brand)] text-white rounded-2xl hover:bg-[var(--brand-hover)] font-bold shadow-lg shadow-[var(--brand-glow)] transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Ingredient
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <SearchFilter v-model="searchTerm" routeName="admin.ingredients.index" placeholder="Find ingredient..." />
                <button
                    @click="toggleLowStockFilter"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-xs font-black uppercase tracking-widest transition-all btn-haptic flex-shrink-0"
                    :class="isLowStockFilter
                        ? 'bg-[var(--danger)]/10 border-[var(--danger)]/40 text-[var(--danger)]'
                        : 'border-[var(--border)] text-[var(--text-muted)] hover:border-[var(--danger)]/40 hover:text-[var(--danger)]'"
                >
                    <span class="w-2 h-2 rounded-full flex-shrink-0" :class="isLowStockFilter ? 'bg-[var(--danger)] animate-pulse' : 'bg-[var(--text-muted)]'"></span>
                    Low Stock Only
                </button>
            </div>

            <!-- Table Card -->
            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                                <th class="px-6 py-4">Stock Status</th>
                                <th class="px-6 py-4">Ingredient Component</th>
                                <th class="px-6 py-4 text-right">Current Supply</th>
                                <th class="px-6 py-4 text-right">Unit Cost</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="ing in ingredients.data" :key="ing.id" class="group hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span :class="ing.is_low_stock ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'" class="block w-2.5 h-2.5 rounded-full shadow-sm"></span>
                                        <span class="text-[10px] font-black uppercase" :class="ing.is_low_stock ? 'text-red-500' : 'text-emerald-600'">
                                            {{ ing.is_low_stock ? 'Low Stock' : 'Sufficient' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[var(--text-strong)]">{{ ing.name }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)] uppercase tracking-tighter">Measure: {{ ing.unit }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="font-mono font-bold" :class="ing.is_low_stock ? 'text-red-500' : 'text-[var(--text-strong)]'">
                                        {{ Number(ing.current_stock).toFixed(2) }}
                                        <span class="text-[10px] font-normal text-[var(--text-muted)]">{{ ing.unit }}</span>
                                    </div>
                                    <div class="text-[10px] text-[var(--text-muted)]">Reorder at {{ Number(ing.reorder_level).toFixed(2) }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                    ${{ Number(ing.cost_per_unit).toFixed(4) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 text-right">
                                        <button @click="openIntake(ing)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-200 text-emerald-600 text-[10px] font-black hover:bg-emerald-50 dark:border-emerald-900/30 dark:hover:bg-emerald-900/10 transition-colors">
                                            INTAKE
                                        </button>
                                        <button @click="openAdjust(ing)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-orange-200 text-orange-600 text-[10px] font-black hover:bg-orange-50 dark:border-orange-900/30 dark:hover:bg-orange-900/10 transition-colors">
                                            ADJUST
                                        </button>
                                        <Link :href="route('admin.ingredients.movements', ing.id)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--border)] text-[var(--text-strong)] text-[10px] font-black hover:bg-[var(--bg-card)] transition-colors">
                                            LEDGER
                                        </Link>
                                        <button @click="deleteIngredient(ing)" class="btn-icon hover:text-red-500" title="Delete Ingredient">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="ingredients.links.length > 3" class="px-6 py-4 bg-[var(--bg-surface)] flex items-center justify-between border-t border-[var(--border)]">
                    <p class="text-xs text-[var(--text-muted)] font-medium">
                        Showing {{ ingredients.from }} to {{ ingredients.to }} of {{ ingredients.total }} ingredients
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in ingredients.links" 
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                            :class="[
                                link.active ? 'bg-[var(--brand)] text-white shadow-md' : 'text-[var(--text-muted)] hover:bg-[var(--bg-card)] hover:text-[var(--text-strong)]',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Ingredient Modal -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showCreateForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center z-50 p-4">
                    <div class="w-full max-w-md glass-panel shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-[var(--border)] bg-[var(--bg-surface)] flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-heading font-black text-[var(--text-strong)]">New Ingredient</h2>
                                <p class="text-xs text-[var(--text-muted)] font-medium mt-0.5">Register a raw material in the inventory supply.</p>
                            </div>
                            <button @click="showCreateForm = false" class="btn-icon w-9 h-9">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Name</label>
                                    <input v-model="createForm.name" placeholder="Sugar, Milk, etc." class="input-premium" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Unit</label>
                                    <input v-model="createForm.unit" placeholder="kg, ltr, pcs" class="input-premium" />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Opening Stock</label>
                                    <input v-model="createForm.current_stock" type="number" step="0.01" class="input-premium font-mono" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Min Level</label>
                                    <input v-model="createForm.reorder_level" type="number" step="0.01" class="input-premium font-mono" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Cost/Unit</label>
                                    <input v-model="createForm.cost_per_unit" type="number" step="0.0001" class="input-premium font-mono" />
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex gap-3">
                            <button @click="showCreateForm = false" class="btn-secondary flex-1 py-3">Cancel</button>
                            <button @click="createIngredient" :disabled="createForm.processing" class="btn-primary flex-1 py-3">Register Entry</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Stock Intake Modal -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showIntakeForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center z-50 p-4">
                    <div class="w-full max-w-md glass-panel shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-emerald-500/20 bg-emerald-500/5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-base font-heading font-black text-emerald-500">Record Stock Intake</h2>
                                <p class="text-xs text-[var(--text-muted)] font-medium truncate">Restocking: <strong class="text-[var(--text-strong)]">{{ intakeIngredient?.name }}</strong></p>
                            </div>
                            <button @click="showIntakeForm = false" class="btn-icon w-9 h-9">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Qty to Add</label>
                                    <div class="relative">
                                        <input v-model="intakeForm.quantity" type="number" step="0.01" placeholder="0.00" class="input-premium pr-12 font-mono" />
                                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-[10px] text-[var(--text-muted)] font-black">{{ intakeIngredient?.unit }}</span>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Cost / {{ intakeIngredient?.unit }}</label>
                                    <input v-model="intakeForm.cost_per_unit" type="number" step="0.0001" class="input-premium font-mono" />
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Supplier</label>
                                <input v-model="intakeForm.supplier" placeholder="Wholesaler / Market Name" class="input-premium" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Ref / Invoice #</label>
                                    <input v-model="intakeForm.reference" placeholder="INV-2024..." class="input-premium" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Date</label>
                                    <input v-model="intakeForm.intake_date" type="date" class="input-premium" />
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex gap-3">
                            <button @click="showIntakeForm = false" class="btn-secondary flex-1 py-3">Cancel</button>
                            <button @click="submitIntake" :disabled="intakeForm.processing" class="flex-1 py-3 btn-haptic bg-emerald-500 text-white font-black rounded-xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 transition-all disabled:opacity-50">Confirm Intake</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Stock Adjustment Modal -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showAdjustForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center z-50 p-4">
                    <div class="w-full max-w-md glass-panel shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-5 border-b border-[var(--warning)]/20 bg-[var(--warning)]/5 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[var(--warning)]/10 border border-[var(--warning)]/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[var(--warning)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-base font-heading font-black text-[var(--warning)]">Manual Adjustment</h2>
                                <p class="text-xs text-[var(--text-muted)] font-medium truncate">Item: <strong class="text-[var(--text-strong)]">{{ adjustIngredient?.name }}</strong></p>
                            </div>
                            <button @click="showAdjustForm = false" class="btn-icon w-9 h-9">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Qty (– for loss)</label>
                                    <div class="relative">
                                        <input v-model="adjustForm.quantity" type="number" step="0.01" class="input-premium pr-12 font-mono" />
                                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-[10px] text-[var(--text-muted)] font-black">{{ adjustIngredient?.unit }}</span>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Reason</label>
                                    <select v-model="adjustForm.type" class="input-premium">
                                        <option value="spoilage">Spoilage</option>
                                        <option value="damage">Damage / Breakage</option>
                                        <option value="staff_meal">Staff Meal</option>
                                        <option value="found">Found / Audit</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Notes</label>
                                <input v-model="adjustForm.notes" placeholder="Explanation for adjustment" class="input-premium" />
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex gap-3">
                            <button @click="showAdjustForm = false" class="btn-secondary flex-1 py-3">Cancel</button>
                            <button @click="submitAdjust" :disabled="adjustForm.processing" class="flex-1 py-3 btn-haptic bg-[var(--warning)] text-white font-black rounded-xl shadow-lg hover:opacity-90 transition-all disabled:opacity-50">Record Adjustment</button>
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
