<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ stocktake: Object });

const completeForm = useForm({});
const showConfirmPanel = ref(false);

function completeAudit() {
    completeForm.post(route('admin.stocktakes.complete', props.stocktake.id));
    showConfirmPanel.value = false;
}

const discrepancyCount = computed(() =>
    props.stocktake.items?.filter(i => Number(i.difference) !== 0).length ?? 0
);

const totalValueImpact = computed(() =>
    props.stocktake.items?.reduce((sum, i) => sum + Number(i.difference) * Number(i.unit_cost), 0) ?? 0
);
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black">Audit Detail: <span class="text-[var(--brand)]">{{ stocktake.reference_number }}</span></h1>
                    <p class="text-sm mt-1">Status:
                        <span class="font-black uppercase tracking-widest text-[10px] px-2 py-0.5 rounded-sm" :class="stocktake.status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'">{{ stocktake.status }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.stocktakes.index')" class="px-4 py-3 bg-[var(--bg-surface)] border border-[var(--border)] rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-[var(--bg-card)] transition-colors">Back to List</Link>
                    <button v-if="stocktake.status === 'draft'" @click="showConfirmPanel = true" :disabled="completeForm.processing" class="px-6 py-3 bg-emerald-500 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg active:scale-95 shadow-emerald-500/20">
                        Approve & Adjust Stock
                    </button>
                    <div v-if="stocktake.status === 'completed'" class="px-4 py-3 bg-[var(--bg-surface)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl font-bold text-xs uppercase tracking-widest">
                        Reconciled at {{ new Date(stocktake.completed_at).toLocaleDateString() }}
                    </div>
                </div>
            </div>

            <!-- Inline Confirmation Panel -->
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showConfirmPanel" class="glass-card border-2 border-emerald-500/30 bg-emerald-500/5 p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-black text-[var(--text-strong)]">Confirm Audit Completion</h3>
                            <p class="text-sm text-[var(--text-muted)] mt-1">
                                This will apply <span class="font-bold text-[var(--text-strong)]">{{ discrepancyCount }} stock adjustment{{ discrepancyCount !== 1 ? 's' : '' }}</span>
                                with a total value impact of
                                <span class="font-bold font-mono" :class="totalValueImpact >= 0 ? 'text-emerald-500' : 'text-[var(--danger)]'">
                                    {{ totalValueImpact >= 0 ? '+' : '' }}${{ Math.abs(totalValueImpact).toFixed(2) }}
                                </span>.
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button @click="showConfirmPanel = false" class="btn-secondary px-4 py-2 text-xs font-bold uppercase tracking-widest">
                            Cancel
                        </button>
                        <button @click="completeAudit" :disabled="completeForm.processing" class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50">
                            {{ completeForm.processing ? 'Applying…' : 'Confirm & Apply' }}
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Summary Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="glass-card p-4 text-center">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Total Items</div>
                    <div class="text-2xl font-black text-[var(--text-strong)]">{{ stocktake.items?.length ?? 0 }}</div>
                </div>
                <div class="glass-card p-4 text-center">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Discrepancies</div>
                    <div class="text-2xl font-black" :class="discrepancyCount > 0 ? 'text-amber-500' : 'text-emerald-500'">{{ discrepancyCount }}</div>
                </div>
                <div class="glass-card p-4 text-center">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Value Impact</div>
                    <div class="text-2xl font-black font-mono" :class="totalValueImpact >= 0 ? 'text-emerald-500' : 'text-[var(--danger)]'">
                        {{ totalValueImpact >= 0 ? '+' : '' }}${{ Math.abs(totalValueImpact).toFixed(2) }}
                    </div>
                </div>
                <div class="glass-card p-4 text-center">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Auditor</div>
                    <div class="text-sm font-bold text-[var(--text-strong)] truncate">{{ stocktake.user?.name ?? '—' }}</div>
                </div>
            </div>

            <div class="glass-card overflow-hidden">
                <div class="p-6 bg-[var(--bg-surface)] border-b border-[var(--border)]">
                    <p class="text-sm"><span class="font-bold text-[var(--text-strong)] uppercase tracking-widest text-[10px]">Notes:</span> <span class="italic text-[var(--text-muted)]">{{ stocktake.notes || 'No notes provided' }}</span></p>
                </div>

                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                            <th class="py-4 px-6 border-b border-[var(--border)]">Ingredient</th>
                            <th class="py-4 px-6 text-right border-b border-[var(--border)]">System Expected</th>
                            <th class="py-4 px-6 text-right border-b border-[var(--border)]">Actual Count</th>
                            <th class="py-4 px-6 text-right border-b border-[var(--border)]">Difference</th>
                            <th class="py-4 px-6 text-right border-b border-[var(--border)]">Value Impact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="item in stocktake.items" :key="item.id" class="hover:bg-[var(--bg-surface)] transition-colors" :class="item.difference !== '0.0000' && item.difference != 0 ? 'bg-orange-50/10 dark:bg-orange-900/5' : ''">
                            <td class="py-4 px-6">
                                <span class="font-bold text-[var(--text-strong)]">{{ item.ingredient?.name || 'Unknown' }}</span>
                                <span class="block text-[10px] text-[var(--text-muted)] uppercase">{{ item.ingredient?.unit }}</span>
                            </td>
                            <td class="py-4 px-6 text-right font-mono">{{ Number(item.expected_quantity).toFixed(2) }}</td>
                            <td class="py-4 px-6 text-right font-mono font-bold">{{ Number(item.actual_quantity).toFixed(2) }}</td>
                            <td class="py-4 px-6 text-right font-mono font-black" :class="item.difference < 0 ? 'text-red-500' : (item.difference > 0 ? 'text-emerald-500' : 'text-[var(--text-muted)]')">
                                {{ item.difference > 0 ? '+' : '' }}{{ Number(item.difference).toFixed(2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono" :class="item.difference < 0 ? 'text-red-500' : (item.difference > 0 ? 'text-emerald-500' : 'text-[var(--text-muted)]')">
                                <span v-if="item.difference != 0">{{ item.difference < 0 ? '-' : '+' }}${{ Number(Math.abs(item.difference) * item.unit_cost).toFixed(2) }}</span>
                                <span v-else class="text-[var(--text-muted)] opacity-50">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
