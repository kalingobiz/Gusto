<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ReportNav from '@/Components/ReportNav.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    from: String, to: String, voids: Object, byUser: Array,
});

const filterForm = useForm({ from: props.from, to: props.to });
function applyFilter() {
    filterForm.get(route('admin.reports.voids'), { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h1 class="text-3xl font-black text-[var(--text-strong)]">Void Log</h1>
                <ReportNav />
            </div>

            <!-- Date filter -->
            <div class="glass-card px-5 py-4 flex items-center gap-3 flex-wrap">
                <input v-model="filterForm.from" type="date" class="input-premium py-1.5 text-sm" />
                <span class="text-[var(--text-muted)] text-xs font-bold uppercase">to</span>
                <input v-model="filterForm.to" type="date" class="input-premium py-1.5 text-sm" />
                <button @click="applyFilter" class="btn-primary px-4 py-2 text-xs font-bold uppercase tracking-widest">Apply</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- By User -->
                <div class="glass-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-[var(--border)] bg-[var(--bg-surface)]">
                        <h2 class="text-xs font-black uppercase tracking-widest text-[var(--text-strong)]">Voids by Staff</h2>
                    </div>
                    <div class="divide-y divide-[var(--border)]">
                        <div v-for="u in byUser" :key="u.name" class="px-5 py-3 flex items-center justify-between hover:bg-[var(--bg-surface)] transition-colors">
                            <span class="text-sm font-bold text-[var(--text-strong)]">{{ u.name }}</span>
                            <span class="font-black font-mono text-sm" :class="u.void_count > 5 ? 'text-[var(--danger)]' : 'text-[var(--text-base)]'">{{ u.void_count }}</span>
                        </div>
                        <div v-if="!byUser?.length" class="px-5 py-6 text-center text-sm text-[var(--text-muted)] italic">No data</div>
                    </div>
                </div>

                <!-- Void records -->
                <div class="lg:col-span-3 glass-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] text-[10px] font-black uppercase tracking-widest border-b border-[var(--border)]">
                                <th class="text-left px-5 py-3">Item</th>
                                <th class="text-left px-5 py-3">Table</th>
                                <th class="text-left px-5 py-3">Reason</th>
                                <th class="text-left px-5 py-3">By</th>
                                <th class="text-left px-5 py-3">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="v in voids?.data" :key="v.id" class="hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-[var(--text-strong)]">{{ v.order_item?.menu_item?.name }}</div>
                                    <div class="text-[10px] text-[var(--text-muted)] font-bold">×{{ v.order_item?.quantity }}</div>
                                </td>
                                <td class="px-5 py-3 font-mono text-[var(--text-base)]">T{{ v.order_item?.order?.restaurant_table?.number }}</td>
                                <td class="px-5 py-3 text-[var(--danger)] font-medium">{{ v.reason }}</td>
                                <td class="px-5 py-3 text-[var(--text-base)]">{{ v.user?.name }}</td>
                                <td class="px-5 py-3 text-[10px] text-[var(--text-muted)] font-mono">{{ new Date(v.created_at).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!voids?.data?.length">
                                <td colspan="5" class="px-5 py-10 text-center text-[var(--text-muted)] italic text-sm">No voids in this period</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
