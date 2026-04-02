<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
    ingredients: Array,
});

const selectedItem = ref(null);
const showAssignForm = ref(false);

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

const allItems = computed(() =>
    props.categories?.flatMap(c => (c.menu_items ?? []).map(i => ({ ...i, category: c }))) ?? []
);
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <h1 class="text-2xl font-bold text-gray-900">Bill of Materials (BOM)</h1>
            <p class="text-sm text-gray-500">Define which ingredients are consumed per serving of each menu item. This drives the anti-theft stock reconciliation.</p>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Item list -->
                <div class="lg:col-span-1 bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Menu Items</div>
                    <div class="divide-y divide-gray-50 max-h-[70vh] overflow-y-auto">
                        <template v-for="cat in categories" :key="cat.id">
                            <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 bg-gray-50 uppercase tracking-wide">{{ cat.name }}</div>
                            <button
                                v-for="item in cat.menu_items"
                                :key="item.id"
                                @click="openItem(item)"
                                class="w-full text-left px-4 py-2.5 text-sm flex items-center justify-between hover:bg-amber-50 transition-colors"
                                :class="selectedItem?.id === item.id ? 'bg-amber-50 border-r-2 border-amber-500' : ''"
                            >
                                <span class="text-gray-900">{{ item.name }}</span>
                                <span class="text-xs text-gray-400">{{ item.bom_items?.length ?? 0 }} ingr.</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- BOM detail panel -->
                <div class="lg:col-span-2">
                    <div v-if="!selectedItem" class="bg-gray-50 rounded-xl border border-dashed border-gray-300 flex items-center justify-center h-40 text-gray-400 text-sm">
                        Select a menu item to view/edit its BOM
                    </div>

                    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="font-semibold text-gray-900">{{ selectedItem.name }}</h2>
                                <div class="text-xs text-gray-400">BOM — ingredients consumed per serving</div>
                            </div>
                            <button @click="openAssign(selectedItem)" class="text-sm bg-amber-500 text-white px-3 py-1.5 rounded-lg hover:bg-amber-600">+ Add Ingredient</button>
                        </div>

                        <div v-if="!selectedItem.bom_items?.length" class="px-4 py-6 text-center text-sm text-gray-400">
                            No ingredients assigned yet. Add ingredients to enable stock tracking.
                        </div>

                        <table v-else class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left px-4 py-2 font-medium text-gray-600">Ingredient</th>
                                    <th class="text-right px-4 py-2 font-medium text-gray-600">Qty/Serving</th>
                                    <th class="text-left px-4 py-2 font-medium text-gray-600">Unit</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="bom in selectedItem.bom_items" :key="bom.id">
                                    <td class="px-4 py-2.5 font-medium text-gray-900">{{ bom.ingredient?.name }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-700">{{ Number(bom.quantity_per_serving).toFixed(4) }}</td>
                                    <td class="px-4 py-2.5 text-gray-500">{{ bom.ingredient?.unit }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <button @click="removeBom(bom)" class="text-xs text-red-500 hover:underline">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign ingredient modal -->
        <div v-if="showAssignForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
                <h2 class="text-lg font-bold">Add Ingredient to BOM</h2>
                <p class="text-sm text-gray-500">Item: <strong>{{ selectedItem?.name }}</strong></p>
                <div>
                    <label class="text-sm text-gray-600">Ingredient *</label>
                    <select v-model="assignForm.ingredient_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="" disabled>Select ingredient...</option>
                        <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }} ({{ ing.unit }})</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Quantity per Serving *</label>
                    <div class="flex items-center gap-2 mt-1">
                        <input v-model="assignForm.quantity_per_serving" type="number" step="0.0001" min="0.0001" placeholder="0.0000" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                        <span class="text-sm text-gray-500">{{ ingredientMap[assignForm.ingredient_id]?.unit ?? '' }}</span>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button @click="showAssignForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl text-sm">Cancel</button>
                    <button @click="saveAssign" :disabled="assignForm.processing" class="flex-1 bg-amber-500 text-white py-2 rounded-xl text-sm font-semibold">Save</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
