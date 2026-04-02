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
        form.post(route('admin.menu.update', props.item.id), { _method: 'put' });
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
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex items-center gap-3">
                <Link :href="route('admin.menu.index')" class="text-gray-400 hover:text-gray-600">←</Link>
                <h1 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Edit' : 'New' }} Menu Item</h1>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <!-- Image upload -->
                <div>
                    <label class="text-sm font-medium text-gray-700">Photo</label>
                    <div class="mt-1 flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 overflow-hidden flex items-center justify-center bg-gray-50">
                            <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
                            <span v-else class="text-2xl">🍽️</span>
                        </div>
                        <input type="file" accept="image/*" @change="onImageChange" class="text-sm text-gray-600" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Category *</label>
                        <select v-model="form.category_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400">
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <div v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Price *</label>
                        <input v-model="form.price" type="number" step="0.01" min="0" placeholder="0.00" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400" />
                        <div v-if="form.errors.price" class="text-xs text-red-500 mt-1">{{ form.errors.price }}</div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Name *</label>
                    <input v-model="form.name" type="text" placeholder="Item name" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-amber-400" />
                    <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Description</label>
                    <textarea v-model="form.description" rows="2" placeholder="Short description" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Available</label>
                    <button
                        type="button"
                        @click="form.is_available = !form.is_available"
                        class="w-10 h-6 rounded-full transition-colors relative"
                        :class="form.is_available ? 'bg-emerald-500' : 'bg-gray-300'"
                    >
                        <span class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all" :class="form.is_available ? 'left-5' : 'left-1'"></span>
                    </button>
                </div>

                <div class="flex gap-3 pt-2">
                    <Link :href="route('admin.menu.index')" class="flex-1 text-center bg-gray-100 text-gray-700 py-2.5 rounded-xl font-medium text-sm">Cancel</Link>
                    <button @click="submit" :disabled="form.processing" class="flex-1 bg-amber-500 text-white py-2.5 rounded-xl font-semibold text-sm hover:bg-amber-600 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : (isEdit ? 'Update Item' : 'Create Item') }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
