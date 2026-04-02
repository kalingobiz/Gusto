<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    ingredients: Array,
});

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
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Ingredients & Stock</h1>
                <div class="flex gap-2">
                    <button @click="showCreateForm = true" class="px-4 py-2 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 font-medium">+ New Ingredient</button>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Ingredient</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Stock</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Reorder At</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Cost/Unit</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="ing in ingredients" :key="ing.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ ing.name }}</div>
                                <div class="text-xs text-gray-400">{{ ing.unit }}</div>
                            </td>
                            <td class="px-4 py-3 text-right" :class="stockClass(ing)">
                                {{ Number(ing.current_stock).toFixed(2) }} {{ ing.unit }}
                                <span v-if="ing.is_low_stock" class="ml-1 text-xs">⚠️</span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ Number(ing.reorder_level).toFixed(2) }} {{ ing.unit }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">${{ Number(ing.cost_per_unit).toFixed(4) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openIntake(ing)" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded hover:bg-emerald-200">+ Stock</button>
                                    <button @click="deleteIngredient(ing)" class="text-xs text-red-500 hover:underline">Del</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Ingredient Modal -->
        <div v-if="showCreateForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-3">
                <h2 class="text-lg font-bold">New Ingredient</h2>
                <input v-model="createForm.name" placeholder="Name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input v-model="createForm.unit" placeholder="Unit (kg, litre, pcs...)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <div class="grid grid-cols-3 gap-2">
                    <input v-model="createForm.current_stock" type="number" step="0.01" placeholder="Stock" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                    <input v-model="createForm.reorder_level" type="number" step="0.01" placeholder="Reorder" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                    <input v-model="createForm.cost_per_unit" type="number" step="0.0001" placeholder="Cost/Unit" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div class="flex gap-2 pt-1">
                    <button @click="showCreateForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl text-sm">Cancel</button>
                    <button @click="createIngredient" :disabled="createForm.processing" class="flex-1 bg-amber-500 text-white py-2 rounded-xl text-sm font-semibold">Save</button>
                </div>
            </div>
        </div>

        <!-- Stock Intake Modal -->
        <div v-if="showIntakeForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-3">
                <h2 class="text-lg font-bold">Record Stock — {{ intakeIngredient?.name }}</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs text-gray-500">Quantity *</label>
                        <input v-model="intakeForm.quantity" type="number" step="0.01" placeholder="0.00" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mt-0.5" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Cost/Unit</label>
                        <input v-model="intakeForm.cost_per_unit" type="number" step="0.0001" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mt-0.5" />
                    </div>
                </div>
                <input v-model="intakeForm.supplier" placeholder="Supplier (optional)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input v-model="intakeForm.reference" placeholder="Reference / Invoice #" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input v-model="intakeForm.intake_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <div class="flex gap-2 pt-1">
                    <button @click="showIntakeForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-xl text-sm">Cancel</button>
                    <button @click="submitIntake" :disabled="intakeForm.processing" class="flex-1 bg-emerald-500 text-white py-2 rounded-xl text-sm font-semibold">Save Intake</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
