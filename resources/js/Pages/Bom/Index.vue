<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
    ingredients: Array,
});

const selectedItem = ref(null);
const showAssignForm = ref(false);
const itemSearch = ref('');

const assignForm = useForm({
    menu_item_id: null,
    ingredient_id: null,
    quantity_per_serving: '',
});

function openItem(item) {
    selectedItem.value = item;
}

function openAssign(item) {
    selectedItem.value = item;
    assignForm.menu_item_id = item.id;
    showAssignForm.value = true;
}

function saveAssign() {
    assignForm.post(route('admin.bom.store'), {
        preserveScroll: true,
        onSuccess: () => { showAssignForm.value = false; assignForm.reset(); },
    });
}

const deleteForm = useForm({});
function removeBom(bom) {
    deleteForm.delete(route('admin.bom.destroy', bom.id), { preserveScroll: true });
}

const ingredientMap = computed(() => {
    const m = {};
    props.ingredients?.forEach(i => { m[i.id] = i; });
    return m;
});

const filteredCategories = computed(() => {
    if (!itemSearch.value) return props.categories;
    
    const search = itemSearch.value.toLowerCase();
    return props.categories.map(cat => ({
        ...cat,
        menu_items: cat.menu_items?.filter(item => 
            item.name.toLowerCase().includes(search)
        )
    })).filter(cat => cat.menu_items?.length > 0);
});

const allItems = computed(() =>
    props.categories?.flatMap(c => (c.menu_items ?? []).map(i => ({ ...i, category: c }))) ?? []
);
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">Recipe Management (BOM)</h1>
                    <p class="text-[var(--text-muted)] text-sm">Define ingredient usage per serving for automated stock reconciliation.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Item Selection List -->
                <div class="lg:col-span-4 space-y-4">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--text-muted)] group-focus-within:text-[var(--brand)] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            v-model="itemSearch"
                            type="text"
                            placeholder="Find menu item..."
                            class="input-premium pl-11 !py-2 text-xs"
                        />
                    </div>

                    <div class="glass-card overflow-hidden">
                        <div class="px-5 py-3 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                            <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Menu Categories</span>
                        </div>
                        <div class="max-h-[60vh] overflow-y-auto divide-y divide-[var(--border)]">
                            <template v-for="cat in filteredCategories" :key="cat.id">
                                <div class="px-5 py-2 text-[10px] font-black text-[var(--brand)] bg-[var(--bg-main)]/50 uppercase tracking-widest">{{ cat.name }}</div>
                                <button
                                    v-for="item in cat.menu_items"
                                    :key="item.id"
                                    @click="openItem(item)"
                                    class="w-full text-left px-5 py-3.5 text-sm flex items-center justify-between transition-all group"
                                    :class="selectedItem?.id === item.id 
                                        ? 'bg-[var(--brand-glow)] border-r-4 border-[var(--brand)] text-[var(--text-strong)]' 
                                        : 'text-[var(--text-base)] hover:bg-[var(--bg-surface)]'"
                                >
                                    <span class="font-bold truncate pr-2">{{ item.name }}</span>
                                    <span class="shrink-0 text-[10px] px-2 py-0.5 rounded-full bg-[var(--bg-surface)] text-[var(--text-muted)] group-hover:bg-[var(--brand)] group-hover:text-white transition-colors">
                                        {{ item.bom_items?.length ?? 0 }}
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- BOM Details Panel -->
                <div class="lg:col-span-8">
                    <div v-if="!selectedItem" class="glass-card border-dashed border-2 p-12 flex flex-col items-center justify-center text-center space-y-4">
                        <div class="w-16 h-16 rounded-3xl bg-[var(--bg-surface)] flex items-center justify-center text-3xl">🍲</div>
                        <div>
                            <h3 class="text-lg font-black italic">Select a Recipe</h3>
                            <p class="text-sm text-[var(--text-muted)]">Choose an item from the left to manage its ingredient breakdown.</p>
                        </div>
                    </div>

                    <div v-else class="glass-card overflow-hidden animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="p-6 border-b border-[var(--border)] flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[var(--bg-surface)]/30">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-[var(--brand)] mb-1 block">{{ selectedItem.category?.name }}</span>
                                <h2 class="text-2xl font-black italic text-[var(--text-strong)]">{{ selectedItem.name }}</h2>
                            </div>
                            <button @click="openAssign(selectedItem)" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[var(--brand)] text-white rounded-xl hover:bg-[var(--brand-hover)] font-bold text-xs shadow-lg shadow-[var(--brand-glow)] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Ingredient
                            </button>
                        </div>

                        <div v-if="!selectedItem.bom_items?.length" class="p-12 text-center space-y-3">
                            <p class="text-sm text-[var(--text-muted)] italic">No ingredients assigned to this recipe yet.</p>
                            <button @click="openAssign(selectedItem)" class="text-xs text-[var(--brand)] font-bold hover:underline underline-offset-4">Configure Recipe Now →</button>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] border-b border-[var(--border)]">
                                        <th class="px-6 py-4 text-left">Ingredient Component</th>
                                        <th class="px-6 py-4 text-right">Quantity</th>
                                        <th class="px-6 py-4 text-left">Unit</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--border)]">
                                    <tr v-for="bom in selectedItem.bom_items" :key="bom.id" class="hover:bg-[var(--bg-surface)]/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-[var(--text-strong)]">{{ bom.ingredient?.name }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-mono bg-[var(--bg-surface)] px-2 py-1 rounded text-xs">{{ Number(bom.quantity_per_serving).toFixed(4) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-[var(--text-muted)] italic">
                                            {{ bom.ingredient?.unit }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button @click="removeBom(bom)" class="btn-icon hover:text-red-500 hover:border-red-500 hover:bg-red-500/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign ingredient modal -->
        <div v-if="showAssignForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200">
            <div class="glass-card shadow-2xl p-8 w-full max-w-md space-y-6">
                <div>
                    <h2 class="text-xl font-black italic">Modify Recipe</h2>
                    <p class="text-xs text-[var(--text-muted)]">Adding component to <strong>{{ selectedItem?.name }}</strong></p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Target Ingredient</label>
                        <select v-model="assignForm.ingredient_id" class="input-premium">
                            <option value="" disabled>Select ingredient...</option>
                            <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }} ({{ ing.unit }})</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Quantity per Serving</label>
                        <div class="relative">
                            <input v-model="assignForm.quantity_per_serving" type="number" step="0.0001" min="0.0001" placeholder="0.0000" class="input-premium pr-16 font-mono" />
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[var(--text-muted)] text-xs italic">
                                {{ ingredientMap[assignForm.ingredient_id]?.unit ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showAssignForm = false" class="flex-1 bg-[var(--bg-surface)] text-[var(--text-strong)] py-3 rounded-2xl text-xs font-bold border border-[var(--border)] hover:bg-[var(--bg-card)] transition-colors">Cancel</button>
                    <button @click="saveAssign" :disabled="assignForm.processing" class="flex-1 bg-[var(--brand)] text-white py-3 rounded-2xl text-sm font-black shadow-lg shadow-[var(--brand-glow)] hover:bg-[var(--brand-hover)] transition-all transition-all">Definite Save</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
