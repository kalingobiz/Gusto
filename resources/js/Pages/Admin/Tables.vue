<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tables: Array,
});

const showCreate = ref(false);
const createForm = useForm({ number: '', capacity: 4, location: '' });

function saveTable() {
    createForm.post(route('admin.tables.store'), {
        preserveScroll: true,
        onSuccess: () => { showCreate.value = false; createForm.reset(); },
    });
}

const deleteForm = useForm({});
function deleteTable(table) {
    if (confirm(`Delete Table ${table.number}? This is irreversible.`)) {
        deleteForm.delete(route('admin.tables.destroy', table.id), { preserveScroll: true });
    }
}

function regenerateQr(table) {
    useForm({}).post(route('admin.tables.qr', table.id), { preserveScroll: true });
}

function qrUrl(table) {
    return table.qr_code_path ? `/storage/${table.qr_code_path}` : null;
}

const statusColors = {
    available: 'bg-emerald-100 text-emerald-700',
    occupied:  'bg-amber-100 text-amber-700',
    reserved:  'bg-blue-100 text-blue-700',
    cleaning:  'bg-gray-100 text-gray-600',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Table Management</h1>
                <button @click="showCreate = true" class="px-4 py-2 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 font-medium">+ New Table</button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                <div v-for="table in tables" :key="table.id" class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-black text-gray-900">T{{ table.number }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="statusColors[table.status]">{{ table.status }}</span>
                    </div>
                    <div class="text-xs text-gray-500">{{ table.capacity }} seats · {{ table.location || 'No location' }}</div>

                    <!-- QR Code -->
                    <div class="flex flex-col items-center gap-2">
                        <img v-if="qrUrl(table)" :src="qrUrl(table)" class="w-20 h-20" alt="QR" />
                        <div v-else class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs">No QR</div>
                        <a v-if="qrUrl(table)" :href="qrUrl(table)" download class="text-xs text-blue-600 hover:underline">Download QR</a>
                    </div>

                    <div class="flex gap-1.5">
                        <button @click="regenerateQr(table)" class="flex-1 text-xs bg-gray-100 text-gray-600 py-1.5 rounded-lg hover:bg-gray-200">Regen QR</button>
                        <button @click="deleteTable(table)" class="text-xs text-red-500 px-2 py-1.5 rounded-lg hover:bg-red-50">Del</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create table modal -->
        <div v-if="showCreate" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-xs space-y-4">
                <h2 class="text-lg font-bold">New Table</h2>
                <div>
                    <label class="text-sm text-gray-600">Table Number *</label>
                    <input v-model="createForm.number" type="text" placeholder="e.g. 1, A1, Bar1" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Capacity</label>
                    <input v-model="createForm.capacity" type="number" min="1" max="50" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-sm text-gray-600">Location</label>
                    <input v-model="createForm.location" type="text" placeholder="Main Hall, Terrace..." class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                </div>
                <div class="flex gap-2 pt-1">
                    <button @click="showCreate = false" class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl text-sm">Cancel</button>
                    <button @click="saveTable" :disabled="createForm.processing" class="flex-1 bg-amber-500 text-white py-2.5 rounded-xl text-sm font-semibold">Create + QR</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
