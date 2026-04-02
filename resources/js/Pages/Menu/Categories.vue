<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
});

const showForm = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    description: '',
    color: '#f59e0b',
    sort_order: 0,
    is_active: true,
});

function editCategory(cat) {
    editingCategory.value = cat;
    form.name = cat.name;
    form.description = cat.description;
    form.color = cat.color || '#f59e0b';
    form.sort_order = cat.sort_order;
    form.is_active = !!cat.is_active;
    showForm.value = true;
}

function resetForm() {
    form.reset();
    form.clearErrors();
    editingCategory.value = null;
    showForm.value = false;
}

function submit() {
    if (editingCategory.value) {
        form.put(route('admin.categories.update', editingCategory.value.id), {
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => resetForm(),
        });
    }
}

const deleteForm = useForm({});
function deleteCategory(cat) {
    if (confirm(`Delete "${cat.name}"? This will not delete the menu items but they will become uncategorized.`)) {
        deleteForm.delete(route('admin.categories.destroy', cat.id));
    }
}
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[var(--text-strong)] caps">Menu Categories</h1>
                    <p class="text-sm text-[var(--text-muted)]">Organize your offerings for better navigation.</p>
                </div>
                <button 
                    @click="showForm = true" 
                    class="px-5 py-2.5 bg-[var(--brand)] text-white rounded-xl font-black text-sm uppercase tracking-widest shadow-xl shadow-[var(--brand-glow)] hover:bg-[var(--brand-hover)] transition-all active:scale-95"
                >
                    + Add Category
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Categories List -->
                <div class="lg:col-span-2 space-y-4">
                    <div 
                        v-for="cat in categories" 
                        :key="cat.id" 
                        class="bg-[var(--bg-card)] border border-[var(--border)] rounded-2xl p-5 flex items-center justify-between group hover:border-[var(--brand)] transition-all shadow-sm"
                    >
                        <div class="flex items-center gap-4 min-w-0">
                            <div 
                                class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl shadow-lg"
                                :style="{ backgroundColor: cat.color || 'var(--brand)' }"
                            >
                                {{ cat.name.charAt(0) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-black text-[var(--text-strong)] flex items-center gap-2">
                                    {{ cat.name }}
                                    <span v-if="!cat.is_active" class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-500 text-[8px] uppercase tracking-widest font-black">Hidden</span>
                                </h3>
                                <p class="text-xs text-[var(--text-muted)] mt-0.5 truncate">{{ cat.description || 'No description provided' }}</p>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="text-[10px] font-black uppercase text-[var(--text-muted)]">
                                        <span class="text-[var(--text-strong)]">{{ cat.menu_items_count }}</span> Items
                                    </div>
                                    <div class="text-[10px] font-black uppercase text-[var(--text-muted)]">
                                        Order: <span class="text-[var(--text-strong)]">{{ cat.sort_order }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="editCategory(cat)" class="p-2.5 rounded-xl border border-[var(--border)] text-[var(--text-muted)] hover:text-blue-500 hover:border-blue-500 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                            <button @click="deleteCategory(cat)" class="p-2.5 rounded-xl border border-[var(--border)] text-[var(--text-muted)] hover:text-red-500 hover:border-red-500 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="!categories?.length" class="bg-[var(--bg-card)] border-2 border-dashed border-[var(--border)] rounded-2xl p-12 text-center">
                        <div class="text-4xl mb-4 opacity-20">📁</div>
                        <h3 class="font-black text-[var(--text-strong)]">No categories defined</h3>
                        <p class="text-sm text-[var(--text-muted)] mt-1">Group your menu items for a cleaner customer experience.</p>
                    </div>
                </div>

                <!-- Form Overlay / Sidebar -->
                <div 
                    v-if="showForm" 
                    class="bg-[var(--bg-card)] border border-[var(--brand)] rounded-3xl p-6 shadow-2xl shadow-[var(--brand-glow)] sticky top-6 animate-in slide-in-from-right-4 duration-300"
                >
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-black tracking-tight text-[var(--text-strong)] caps">{{ editingCategory ? 'Edit Category' : 'New Category' }}</h2>
                        <button @click="resetForm" class="text-[var(--text-muted)] hover:text-[var(--text-strong)] transition-colors">✕</button>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">Category Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. Signature Mains" class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent outline-none transition-all" />
                            <div v-if="form.errors.name" class="text-[10px] text-red-500 font-bold uppercase">{{ form.errors.name }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">Visual Label Color</label>
                            <div class="flex gap-2">
                                <input v-model="form.color" type="color" class="w-12 h-12 rounded-xl bg-transparent border-0 p-0 overflow-hidden cursor-pointer" />
                                <input v-model="form.color" type="text" class="flex-1 bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-2 uppercase font-black text-xs outline-none" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">Description</label>
                            <textarea v-model="form.description" rows="2" class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent outline-none transition-all resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">Order Index</label>
                                <input v-model="form.sort_order" type="number" class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 outline-none" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">Visibility</label>
                                <button
                                    @click="form.is_active = !form.is_active"
                                    class="w-full py-3 rounded-xl border border-[var(--border)] font-black text-[10px] uppercase tracking-widest transition-all"
                                    :class="form.is_active ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-red-500/10 text-red-600 border-red-500/20'"
                                >
                                    {{ form.is_active ? 'Visible' : 'Hidden' }}
                                </button>
                            </div>
                        </div>

                        <button 
                            @click="submit" 
                            :disabled="form.processing"
                            class="w-full py-4 bg-[var(--text-strong)] text-[var(--bg-main)] rounded-2xl font-black uppercase tracking-widest text-xs hover:opacity-90 disabled:opacity-50 transition-all active:scale-[0.98] shadow-xl"
                        >
                            {{ form.processing ? 'Transmitting...' : (editingCategory ? 'Update Category' : 'Confirm Creation') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
