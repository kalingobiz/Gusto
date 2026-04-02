<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ users: Array });

const showForm = ref(false);
const editingUser = ref(null);
const form = useForm({ name: '', email: '', role: 'cashier', password: '', is_active: true });

function openCreate() {
    editingUser.value = null;
    form.reset();
    showForm.value = true;
}

function openEdit(user) {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.is_active = user.is_active;
    form.password = '';
    showForm.value = true;
}

function save() {
    if (editingUser.value) {
        form.put(route('admin.users.update', editingUser.value.id), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; },
        });
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); },
        });
    }
}

const deleteForm = useForm({});
function deleteUser(user) {
    if (confirm(`Delete ${user.name}?`)) {
        deleteForm.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    }
}

const roleColors = {
    admin:   'bg-purple-100 text-purple-700',
    cashier: 'bg-blue-100 text-blue-700',
    kitchen: 'bg-amber-100 text-amber-700',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Staff Accounts</h1>
                <button @click="openCreate" class="px-4 py-2 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 font-medium">+ Add Staff</button>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Role</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Active</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ user.name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ user.email }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" :class="roleColors[user.role]">{{ user.role }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="user.is_active ? 'text-emerald-600' : 'text-gray-400'">{{ user.is_active ? '●' : '○' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(user)" class="text-xs text-blue-600 hover:underline">Edit</button>
                                    <button @click="deleteUser(user)" class="text-xs text-red-500 hover:underline">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
                <h2 class="text-lg font-bold">{{ editingUser ? 'Edit' : 'New' }} Staff Account</h2>
                <input v-model="form.name" placeholder="Full Name *" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <input v-model="form.email" type="email" placeholder="Email *" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <select v-model="form.role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="admin">Admin (Owner/Manager)</option>
                    <option value="cashier">Cashier / Waiter</option>
                    <option value="kitchen">Kitchen Staff</option>
                </select>
                <input v-model="form.password" type="password" :placeholder="editingUser ? 'New password (leave blank to keep)' : 'Password *'" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" />
                <div v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-500">{{ error }}</div>
                <div class="flex gap-2 pt-1">
                    <button @click="showForm = false" class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl text-sm">Cancel</button>
                    <button @click="save" :disabled="form.processing" class="flex-1 bg-amber-500 text-white py-2.5 rounded-xl text-sm font-semibold">Save</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
