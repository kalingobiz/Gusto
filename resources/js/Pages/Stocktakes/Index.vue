<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ stocktakes: Object });
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">Stock Audits</h1>
                    <p class="text-[var(--text-muted)] text-sm">Physical inventory counts and corrections.</p>
                </div>
                <Link :href="route('admin.stocktakes.create')" class="inline-flex justify-center px-5 py-3 bg-[var(--brand)] text-white rounded-2xl hover:bg-[var(--brand-hover)] font-bold transition-all shadow-[var(--brand-glow)] active:scale-95">
                    New Physical Audit
                </Link>
            </div>

            <div class="glass-card overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                            <th class="px-6 py-4">Reference</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Auditor</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="audit in stocktakes.data" :key="audit.id" class="hover:bg-[var(--bg-surface)] transition-colors">
                            <td class="px-6 py-4 font-mono font-bold">{{ audit.reference_number }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest', audit.status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400']">
                                    {{ audit.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono">{{ new Date(audit.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-xs font-medium">{{ audit.user?.name || 'Unknown' }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.stocktakes.show', audit.id)" class="px-3 py-1.5 rounded-lg border border-[var(--border)] text-[var(--text-strong)] hover:bg-[var(--bg-surface)] text-[10px] font-black uppercase tracking-widest transition-colors inline-block">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="stocktakes.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-[var(--text-muted)]">
                                No stock audits found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="stocktakes.links.length > 3" class="flex gap-1 justify-end">
                <Link v-for="link in stocktakes.links" :key="link.label" :href="link.url || '#'" v-html="link.label" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold"
                    :class="[link.active ? 'bg-[var(--brand)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-strong)]', !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[var(--bg-card)]']"
                />
            </div>
        </div>
    </AppLayout>
</template>
