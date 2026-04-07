<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    item: Object,
    categories: Array,
});

const isEdit = !!props.item;

const form = useForm({
    category_id: props.item?.category_id ?? props.categories?.[0]?.id,
    name: props.item?.name ?? '',
    description: props.item?.description ?? '',
    price: props.item?.price ?? '',
    is_available: props.item?.is_available ?? true,
    sort_order: props.item?.sort_order ?? 0,
    image: null,
});

const previewUrl = ref(props.item?.image_path ? `/storage/${props.item.image_path}` : null);

function onImageChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        previewUrl.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (isEdit) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('admin.menu.update', props.item.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('admin.menu.store'));
    }
}

// Variants
const variants = ref(props.item?.variants ?? []);
const newVariant = ref({ name: '', price_modifier: '' });
const variantForm = useForm({});

function addVariant() {
    if (!newVariant.value.name) return;
    variantForm.transform(() => ({
        menu_item_id: props.item?.id,
        name: newVariant.value.name,
        price_modifier: newVariant.value.price_modifier,
    })).post('/admin/menu-variants', { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.menu.index')" class="p-2 rounded-xl bg-[var(--bg-card)] border border-[var(--border)] text-[var(--text-muted)] hover:text-[var(--brand)] transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[var(--text-strong)]">{{ isEdit ? 'Edit' : 'New' }} Menu Item</h1>
                    <p class="text-sm text-[var(--text-muted)]">{{ isEdit ? 'Modify existing dish details' : 'Add a new culinary delight to your menu' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Form Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-6 shadow-sm space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Item Name *</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    placeholder="e.g. Wagyu Truffle Burger" 
                                    class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none" 
                                />
                                <div v-if="form.errors.name" class="text-xs text-red-500 font-medium">{{ form.errors.name }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Category *</label>
                                <select 
                                    v-model="form.category_id" 
                                    class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none appearance-none"
                                >
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <div v-if="form.errors.category_id" class="text-xs text-red-500 font-medium">{{ form.errors.category_id }}</div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Description</label>
                            <textarea 
                                v-model="form.description" 
                                rows="3" 
                                placeholder="Describe the flavors, ingredients, or preparation method..." 
                                class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Base Price *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]">$</span>
                                    <input 
                                        v-model="form.price" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        placeholder="0.00" 
                                        class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl pl-8 pr-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none" 
                                    />
                                </div>
                                <div v-if="form.errors.price" class="text-xs text-red-500 font-medium">{{ form.errors.price }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)]">Sort Order</label>
                                <input 
                                    v-model="form.sort_order" 
                                    type="number" 
                                    class="w-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] rounded-xl px-4 py-3 focus:ring-2 focus:ring-[var(--brand)] focus:border-transparent transition-all outline-none" 
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <Link :href="route('admin.menu.index')" class="flex-1 text-center bg-[var(--bg-surface)] text-[var(--text-strong)] py-4 rounded-2xl font-bold border border-[var(--border)] hover:bg-[var(--border)] transition-all">
                            Discard Changes
                        </Link>
                        <button 
                            @click="submit" 
                            :disabled="form.processing" 
                            class="flex-[2] bg-[var(--brand)] text-white py-4 rounded-2xl font-black text-lg hover:bg-[var(--brand-hover)] shadow-xl shadow-[var(--brand-glow)] disabled:opacity-50 transition-all active:scale-[0.98]"
                        >
                            {{ form.processing ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create Item') }}
                        </button>
                    </div>
                </div>

                <!-- Right Column: Visuals & Status -->
                <div class="space-y-6">
                    <!-- Image Preview -->
                    <div class="bg-[var(--bg-card)] rounded-3xl border border-[var(--border)] p-6 shadow-sm">
                        <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)] mb-4 block">Item Photography</label>
                        <div class="aspect-square rounded-2xl border-2 border-dashed border-[var(--border)] overflow-hidden flex flex-col items-center justify-center bg-[var(--bg-surface)] relative group">
                            <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                            <div v-else class="flex flex-col items-center gap-2 opacity-40">
                                <span class="text-5xl">📸</span>
                                <span class="text-xs font-bold">No photo uploaded</span>
                            </div>
                            
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                <span class="text-white text-xs font-bold uppercase tracking-widest">Change Photo</span>
                            </div>
                            
                            <input 
                                type="file" 
                                accept="image/*" 
                                @change="onImageChange" 
                                class="absolute inset-0 opacity-0 cursor-pointer" 
                            />
                        </div>
                        <p class="text-[10px] text-[var(--text-muted)] mt-4 leading-relaxed text-center italic">
                            High-quality photos increase sales by up to 30%. Use clear, well-lit shots of the dish.
                        </p>
                    </div>

                    <!-- Status Card -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-[var(--text-strong)]">Availability Status</label>
                            <button
                                type="button"
                                @click="form.is_available = !form.is_available"
                                class="w-12 h-6 rounded-full transition-all relative outline-none"
                                :class="form.is_available ? 'bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-[var(--border)]'"
                            >
                                <span class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all shadow-sm" :class="form.is_available ? 'left-7' : 'left-1'"></span>
                            </button>
                        </div>
                        <div class="p-3 rounded-xl text-xs font-medium" :class="form.is_available ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600'">
                            {{ form.is_available ? 'This item is visible to customers and available for ordering.' : 'This item is hidden from the customer menu.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
