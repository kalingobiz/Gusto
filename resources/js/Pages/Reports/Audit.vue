<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String, to: String, logs: Object,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.audit'), { preserveScroll: true });
}

const actionBadgeClass = {
    created:        'bg-[var(--info)]/10 text-[var(--info)] border-[var(--info)]/20',
    status_changed: 'bg-[var(--warning)]/10 text-[var(--warning)] border-[var(--warning)]/20',
    voided:         'bg-[var(--danger)]/10 text-[var(--danger)] border-[var(--danger)]/20',
    qty_modified:   'bg-orange-500/10 text-orange-500 border-orange-500/20',
    price_modified: 'bg-purple-500/10 text-purple-500 border-purple-500/20',
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Audit Trail</h1>
                <p class="text-sm text-[var(--text-muted)]">Complete immutable log of every order item action. Use this to investigate discrepancies.</p>
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                <span class="text-[var(--text-muted)] text-xs font-bold uppercase">to</span>
                <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <!-- Table -->
            <div class="glass-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                            <th class="text-left px-5 py-4">Time</th>
                            <th class="text-left px-5 py-4">Item</th>
                            <th class="text-left px-5 py-4">Table</th>
                            <th class="text-center px-5 py-4">Action</th>
                            <th class="text-left px-5 py-4">Status Change</th>
                            <th class="text-left px-5 py-4">By</th>
                            <th class="text-left px-5 py-4">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="log in logs?.data" :key="log.id" class="text-xs hover:bg-[var(--bg-surface)] transition-colors">
                            <td class="px-5 py-3 text-[var(--text-muted)] font-mono">{{ new Date(log.created_at).toLocaleString() }}</td>
                            <td class="px-5 py-3 font-bold text-[var(--text-strong)]">{{ log.order_item?.menu_item?.name }}</td>
                            <td class="px-5 py-3 font-mono text-[var(--text-base)]">T{{ log.order_item?.order?.restaurant_table?.number }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black uppercase border" :class="actionBadgeClass[log.action] ?? 'bg-[var(--bg-surface)] text-[var(--text-muted)] border-[var(--border)]'">{{ log.action }}</span>
                            </td>
                            <td class="px-5 py-3 text-[var(--text-base)]">
                                <span v-if="log.from_status" class="font-mono">{{ log.from_status }} → {{ log.to_status }}</span>
                                <span v-else class="text-[var(--text-muted)]">—</span>
                            </td>
                            <td class="px-5 py-3 text-[var(--text-base)] font-medium">{{ log.user?.name ?? 'Customer' }}</td>
                            <td class="px-5 py-3 text-[var(--text-muted)] font-mono">{{ log.ip_address }}</td>
                        </tr>
                        <tr v-if="!logs?.data?.length">
                            <td colspan="7" class="px-5 py-10 text-center text-[var(--text-muted)] italic">No audit records found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
