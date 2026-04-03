<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    ingredient: Object,
    movements: Object,
});

function getSourceBadge(movement) {
    if (!movement.source_type) return { text: 'System', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' };
    if (movement.source_type.includes('StockIntake')) return { text: 'Intake', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' };
    if (movement.source_type.includes('StockDeduction')) return { text: 'Sale Deduction', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' };
    if (movement.source_type.includes('StockAdjustment')) return { text: 'Adjustment', class: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' };
    return { text: 'Other', class: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' };
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.ingredients.index')" class="p-2 rounded-xl bg-[var(--bg-surface)] hover:bg-[var(--bg-card)] border border-[var(--border)] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </Link>
                <div>
                    <h1 class="text-3xl font-black">Stock Ledger: {{ ingredient.name }}</h1>
                    <p class="text-[var(--text-muted)] text-sm">Chronological history of all stock movements.</p>
                </div>
            </div>

            <div class="glass-card overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Source</th>
                            <th class="px-6 py-4 text-right">Quantity Change</th>
                            <th class="px-6 py-4 text-right">Balance After</th>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-[var(--bg-surface)] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                {{ new Date(movement.created_at).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest', getSourceBadge(movement).class]">
                                    {{ getSourceBadge(movement).text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-right" :class="movement.quantity_change > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-orange-600 dark:text-orange-500'">
                                {{ movement.quantity_change > 0 ? '+' : '' }}{{ Number(movement.quantity_change).toFixed(4) }} <span class="text-[10px] text-[var(--text-muted)] font-normal">{{ ingredient.unit }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-right">
                                {{ Number(movement.balance_after).toFixed(4) }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                {{ movement.user ? movement.user.name : 'System' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-[var(--text-muted)]">
                                {{ movement.notes || '-' }}
                            </td>
                        </tr>
                        <tr v-if="movements.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-[var(--text-muted)]">
                                No movements recorded yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div v-if="movements.links.length > 3" class="flex gap-1 justify-end">
                <Link v-for="link in movements.links" :key="link.label" :href="link.url || '#'" v-html="link.label" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold"
                    :class="[link.active ? 'bg-[var(--brand)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-strong)]', !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[var(--bg-card)]']"
                />
            </div>
        </div>
    </AppLayout>
</template>
