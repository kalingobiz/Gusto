<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
});

const showItemForm = ref(false);
const editingItem = ref(null);
const selectedCategoryFilter = ref(null);

function editItem(item) {
    editingItem.value = item;
    showItemForm.value = true;
}

function closeForm() {
    showItemForm.value = false;
    editingItem.value = null;
}

const deleteForm = useForm({});
function deleteItem(item) {
    if (confirm(`Delete "${item.name}"?`)) {
        deleteForm.delete(route('admin.menu.destroy', item.id));
    }
}

function toggleAvailable(item) {
    useForm({ is_available: !item.is_available })
        .put(route('admin.menu.update', item.id), { preserveScroll: true });
}

function currency(v) { return Number(v).toFixed(2); }

const allItems = props.categories?.flatMap(c => c.menu_items?.map(i => ({ ...i, category: c })) ?? []) ?? [];
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Menu Management</h1>
                <div class="flex gap-2">
                    <Link :href="route('admin.categories.index')" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Categories</Link>
                    <Link :href="route('admin.menu.create')" class="px-4 py-2 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 font-medium">+ New Item</Link>
                </div>
            </div>

            <!-- Category filter chips -->
            <div class="flex gap-2 flex-wrap">
                <button
                    @click="selectedCategoryFilter = null"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                    :class="!selectedCategoryFilter ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                >All</button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCategoryFilter = cat.id"
                    class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                    :class="selectedCategoryFilter === cat.id ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                >{{ cat.name }}</button>
            </div>

            <!-- Items table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Item</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Category</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Price</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Variants</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Available</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template v-for="cat in categories" :key="cat.id">
                            <tr
                                v-for="item in cat.menu_items"
                                :key="item.id"
                                v-show="!selectedCategoryFilter || selectedCategoryFilter === cat.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-8 h-8 rounded-lg object-cover" />
                                        <div v-else class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 text-xs">🍽️</div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ item.name }}</div>
                                            <div v-if="item.description" class="text-xs text-gray-400 truncate max-w-xs">{{ item.description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ cat.name }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">${{ currency(item.price) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-gray-500">{{ item.variants?.length ?? 0 }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        @click="toggleAvailable(item)"
                                        class="w-10 h-6 rounded-full transition-colors relative"
                                        :class="item.is_available ? 'bg-emerald-500' : 'bg-gray-300'"
                                    >
                                        <span class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all" :class="item.is_available ? 'left-5' : 'left-1'"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('admin.menu.edit', item.id)" class="text-xs text-blue-600 hover:underline">Edit</Link>
                                        <button @click="deleteItem(item)" class="text-xs text-red-500 hover:underline">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
