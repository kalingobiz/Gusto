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
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[var(--text-strong)]">Menu Management</h1>
                    <p class="text-sm text-[var(--text-muted)]">Manage your dishes, drinks, and categories.</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.categories.index')" class="px-4 py-2 text-sm bg-[var(--bg-card)] text-[var(--text-strong)] border border-[var(--border)] rounded-xl hover:border-[var(--brand)] transition-all">Categories</Link>
                    <Link :href="route('admin.menu.create')" class="px-4 py-2 text-sm bg-[var(--brand)] text-white rounded-xl hover:bg-[var(--brand-hover)] font-bold shadow-lg shadow-[var(--brand-glow)] transition-all">+ New Item</Link>
                </div>
            </div>

            <!-- Category filter chips -->
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button
                    @click="selectedCategoryFilter = null"
                    class="shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all border"
                    :class="!selectedCategoryFilter 
                        ? 'bg-[var(--brand)] text-white border-transparent' 
                        : 'bg-[var(--bg-card)] text-[var(--text-muted)] border-[var(--border)] hover:border-[var(--brand)]'"
                >All Items</button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="selectedCategoryFilter = cat.id"
                    class="shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all border"
                    :class="selectedCategoryFilter === cat.id 
                        ? 'bg-[var(--brand)] text-white border-transparent' 
                        : 'bg-[var(--bg-card)] text-[var(--text-muted)] border-[var(--border)] hover:border-[var(--brand)]'"
                >{{ cat.name }}</button>
            </div>

            <!-- Items table -->
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] overflow-hidden shadow-sm">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] border-b border-[var(--border)]">
                            <th class="text-left px-6 py-4 font-bold text-[var(--text-strong)] uppercase tracking-wider text-[10px]">Item Details</th>
                            <th class="text-left px-6 py-4 font-bold text-[var(--text-strong)] uppercase tracking-wider text-[10px] hidden md:table-cell">Category</th>
                            <th class="text-right px-6 py-4 font-bold text-[var(--text-strong)] uppercase tracking-wider text-[10px]">Price</th>
                            <th class="text-center px-6 py-4 font-bold text-[var(--text-strong)] uppercase tracking-wider text-[10px] hidden sm:table-cell">Status</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <template v-for="cat in categories" :key="cat.id">
                            <tr
                                v-for="item in cat.menu_items"
                                :key="item.id"
                                v-show="!selectedCategoryFilter || selectedCategoryFilter === cat.id"
                                class="hover:bg-[var(--bg-surface)]/50 transition-colors group"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <img v-if="item.image_path" :src="`/storage/${item.image_path}`" class="w-12 h-12 rounded-xl object-cover ring-2 ring-transparent group-hover:ring-[var(--brand-glow)] transition-all" />
                                            <div v-else class="w-12 h-12 rounded-xl bg-[var(--bg-surface)] flex items-center justify-center text-xl grayscale group-hover:grayscale-0 transition-all">🍽️</div>
                                            <div v-if="!item.is_available" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 border-2 border-[var(--bg-card)] rounded-full"></div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-[var(--text-strong)] truncate">{{ item.name }}</div>
                                            <div v-if="item.description" class="text-xs text-[var(--text-muted)] truncate max-w-[200px] sm:max-w-xs">{{ item.description }}</div>
                                            <div class="md:hidden mt-1 text-[10px] font-bold text-[var(--brand)] uppercase">{{ cat.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[var(--text-muted)] font-medium hidden md:table-cell">
                                    <span class="px-2.5 py-1 rounded-lg bg-[var(--bg-surface)] border border-[var(--border)] text-xs">
                                        {{ cat.name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-black text-[var(--text-strong)]">${{ currency(item.price) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center hidden sm:table-cell">
                                    <button
                                        @click="toggleAvailable(item)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase transition-all"
                                        :class="item.is_available 
                                            ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' 
                                            : 'bg-red-500/10 text-red-600 border border-red-500/20'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_available ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                                        {{ item.is_available ? 'Active' : 'Unavailable' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3 translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">
                                        <Link :href="route('admin.menu.edit', item.id)" class="p-2 rounded-lg text-blue-500 hover:bg-blue-500/10 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>
                                        <button @click="deleteItem(item)" class="p-2 rounded-lg text-red-500 hover:bg-red-500/10 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
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
