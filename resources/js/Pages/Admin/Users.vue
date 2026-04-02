<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ 
    users: Object, // Changed to Object for pagination
    filters: Object 
});

const searchTerm = ref(props.filters.search || '');

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
    admin:   'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900/30',
    cashier: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900/30',
    kitchen: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/30',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header & Actions -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">Staff Management</h1>
                    <p class="text-[var(--text-muted)] text-sm">Manage access and roles for your team members.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--brand)] text-white rounded-2xl hover:bg-[var(--brand-hover)] font-bold shadow-lg shadow-[var(--brand-glow)] transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Team Member
                </button>
            </div>

            <!-- Filters -->
            <div class="flex items-center justify-between">
                <SearchFilter v-model="searchTerm" routeName="admin.users.index" placeholder="Search by name or email..." />
            </div>

            <!-- Table Card -->
            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-left">Team Member</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="user in users.data" :key="user.id" class="group hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-6 py-4">
                                    <span :class="user.is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-gray-400'" class="block w-2.5 h-2.5 rounded-full"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-[var(--text-strong)]">{{ user.name }}</span>
                                        <span class="text-xs text-[var(--text-muted)]">{{ user.email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border" :class="roleColors[user.role]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit(user)" class="btn-icon" title="Edit Staff">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteUser(user)" class="btn-icon hover:text-red-500 hover:border-red-500 hover:bg-red-500/10" title="Delete Staff">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links.length > 3" class="px-6 py-4 bg-[var(--bg-surface)] flex items-center justify-between border-t border-[var(--border)]">
                    <p class="text-xs text-[var(--text-muted)] font-medium">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} members
                    </p>
                    <div class="flex gap-1">
                        <Link 
                            v-for="link in users.links" 
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                            :class="[
                                link.active ? 'bg-[var(--brand)] text-white shadow-md' : 'text-[var(--text-muted)] hover:bg-[var(--bg-card)] hover:text-[var(--text-strong)]',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="showForm" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200">
            <div class="glass-card shadow-2xl p-8 w-full max-w-md space-y-6">
                <div>
                    <h2 class="text-xl font-black">{{ editingUser ? 'Modify' : 'New' }} Staff Member</h2>
                    <p class="text-xs text-[var(--text-muted)]">Please enter the correct details for the team member.</p>
                </div>
                
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Full Name</label>
                        <input v-model="form.name" placeholder="Staff Name" class="input-premium" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Email Address</label>
                        <input v-model="form.email" type="email" placeholder="email@gusto.com" class="input-premium" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">System Role</label>
                        <select v-model="form.role" class="input-premium">
                            <option value="admin">Admin (Owner/Manager)</option>
                            <option value="cashier">Cashier / Waiter</option>
                            <option value="kitchen">Kitchen Staff</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Password</label>
                        <input v-model="form.password" type="password" :placeholder="editingUser ? '••••••••' : 'Password'" class="input-premium" />
                        <p v-if="editingUser" class="text-[10px] text-[var(--text-muted)] pl-1">Leave blank to keep existing password.</p>
                    </div>
                </div>

                <div v-for="(error, field) in form.errors" :key="field" class="text-xs text-red-500 italic">⚠️ {{ error }}</div>
                
                <div class="flex gap-3 pt-2">
                    <button @click="showForm = false" class="flex-1 bg-[var(--bg-surface)] text-[var(--text-strong)] py-3 rounded-2xl text-xs font-bold border border-[var(--border)] hover:bg-[var(--bg-card)] transition-colors">Cancel</button>
                    <button @click="save" :disabled="form.processing" class="flex-1 bg-[var(--brand)] text-white py-3 rounded-2xl text-sm font-black shadow-lg shadow-[var(--brand-glow)] hover:bg-[var(--brand-hover)] transition-all">Save Member</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
