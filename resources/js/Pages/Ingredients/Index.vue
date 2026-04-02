<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    ingredients: Object, // Changed to Object for pagination
    filters: Object
});

const searchTerm = ref(props.filters.search || '');

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

const deleteForm = useForm({});
function deleteIngredient(ingredient) {
    if (confirm(`Delete "${ingredient.name}"?`)) {
        deleteForm.delete(route('admin.ingredients.destroy', ingredient.id), { preserveScroll: true });
    }
}

function stockClass(ing) {
    return ing.is_low_stock ? 'text-red-600 font-bold' : 'text-gray-900';
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
                <button @click="showCreateForm = true" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--brand)] text-white rounded-2xl hover:bg-[var(--brand-hover)] font-bold shadow-lg shadow-[var(--brand-glow)] transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Ingredient
                </button>
            </div>

            <!-- Filters -->
            <div class="flex items-center justify-between">
                <SearchFilter v-model="searchTerm" routeName="admin.ingredients.index" placeholder="Find ingredient..." />
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
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            INTAKE
                                        </button>
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
        <div v-if="showCreateForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200">
            <div class="glass-card shadow-2xl p-8 w-full max-w-md space-y-6">
                <div>
                    <h2 class="text-xl font-black italic">Catalog Component</h2>
                    <p class="text-xs text-[var(--text-muted)]">Register a new raw material in the inventory supply.</p>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Name</label>
                            <input v-model="createForm.name" placeholder="Sugar, Milk, etc." class="input-premium" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Unit</label>
                            <input v-model="createForm.unit" placeholder="kg, lite, pcs" class="input-premium" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Cur. Stock</label>
                            <input v-model="createForm.current_stock" type="number" step="0.01" class="input-premium font-mono" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Min Level</label>
                            <input v-model="createForm.reorder_level" type="number" step="0.01" class="input-premium font-mono" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Cost/Unit</label>
                            <input v-model="createForm.cost_per_unit" type="number" step="0.0001" class="input-premium font-mono text-emerald-600" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showCreateForm = false" class="flex-1 bg-[var(--bg-surface)] text-[var(--text-strong)] py-3 rounded-2xl text-xs font-bold border border-[var(--border)] hover:bg-[var(--bg-card)] transition-colors">Cancel</button>
                    <button @click="createIngredient" :disabled="createForm.processing" class="flex-1 bg-[var(--brand)] text-white py-3 rounded-2xl text-sm font-black shadow-lg shadow-[var(--brand-glow)]">Register Entry</button>
                </div>
            </div>
        </div>

        <!-- Stock Intake Modal -->
        <div v-if="showIntakeForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200">
            <div class="glass-card shadow-2xl p-8 w-full max-w-md space-y-6 border-emerald-500/20">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl">📥</div>
                    <div>
                        <h2 class="text-xl font-black italic">Record Intake</h2>
                        <p class="text-xs text-[var(--text-muted)]">Restocking: <strong>{{ intakeIngredient?.name }}</strong></p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Qty to ADD</label>
                            <div class="relative">
                                <input v-model="intakeForm.quantity" type="number" step="0.01" placeholder="0.00" class="input-premium pr-12 font-mono" />
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-[10px] text-[var(--text-muted)] font-black">{{ intakeIngredient?.unit }}</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Latest Cost/{{ intakeIngredient?.unit }}</label>
                            <input v-model="intakeForm.cost_per_unit" type="number" step="0.0001" class="input-premium font-mono text-emerald-600" />
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Supplier Selection</label>
                        <input v-model="intakeForm.supplier" placeholder="Wholesaler / Market Name" class="input-premium" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Ref / Invoice #</label>
                            <input v-model="intakeForm.reference" placeholder="INV-2024..." class="input-premium text-xs" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Transaction Date</label>
                            <input v-model="intakeForm.intake_date" type="date" class="input-premium text-xs" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showIntakeForm = false" class="flex-1 bg-[var(--bg-surface)] text-[var(--text-strong)] py-3 rounded-2xl text-xs font-bold border border-[var(--border)]">Cancel</button>
                    <button @click="submitIntake" :disabled="intakeForm.processing" class="flex-1 bg-emerald-500 text-white py-3 rounded-2xl text-[10px] font-black shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 uppercase tracking-widest transition-all">Definite Intake</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
