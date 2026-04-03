<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ ingredients: Array });

const form = useForm({
    notes: '',
    items: props.ingredients.map(ing => ({
        ingredient_id: ing.id,
        name: ing.name,
        unit: ing.unit,
        expected: ing.current_stock,
        actual_quantity: ing.current_stock,
    }))
});

function submit() {
    form.post(route('admin.stocktakes.store'));
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black">Perform Stock Audit</h1>
                    <p class="text-[var(--text-muted)] text-sm">Enter actual physical quantities for your ingredients.</p>
                </div>
            </div>

            <div class="glass-card p-6 border-emerald-500/10">
                <div class="mb-8">
                    <label class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] pl-1">Audit Notes (optional)</label>
                    <input v-model="form.notes" class="input-premium w-full mt-1 border border-[var(--border)]" placeholder="E.g., End of month physical count..." />
                </div>

                <div class="overflow-x-auto rounded-xl border border-[var(--border)]">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[var(--bg-surface)] text-[var(--text-muted)] uppercase text-[10px] tracking-widest font-black">
                                <th class="py-4 px-4">Ingredient</th>
                                <th class="py-4 px-4 text-right">System Qty</th>
                                <th class="py-4 px-4">Actual Count</th>
                                <th class="py-4 px-4 text-right">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            <tr v-for="(item, idx) in form.items" :key="item.ingredient_id" class="hover:bg-[var(--bg-surface)] transition-colors">
                                <td class="py-4 px-4">
                                    <span class="font-bold text-[var(--text-strong)]">{{ item.name }}</span>
                                    <span class="block text-[10px] text-[var(--text-muted)] uppercase">{{ item.unit }}</span>
                                </td>
                                <td class="py-4 px-4 text-right font-mono text-[var(--text-strong)] font-bold">
                                    {{ Number(item.expected).toFixed(2) }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.01" v-model="item.actual_quantity" class="w-32 px-3 py-2 rounded-lg bg-[var(--bg-surface)] border border-[var(--border)] font-mono text-[var(--text-strong)] focus:ring-2 focus:ring-[var(--brand)] focus:border-[var(--brand)] transition-shadow" />
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-bold" :class="(item.actual_quantity - item.expected) < 0 ? 'text-red-500' : ((item.actual_quantity - item.expected) > 0 ? 'text-emerald-500' : 'text-[var(--text-muted)]')">
                                    {{ (item.actual_quantity - item.expected) > 0 ? '+' : '' }}{{ Number(item.actual_quantity - item.expected).toFixed(2) }}
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-[var(--text-muted)]">
                                    No ingredients available to audit.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <Link :href="route('admin.stocktakes.index')" class="px-5 py-3 bg-[var(--bg-surface)] text-[var(--text-strong)] rounded-xl border border-[var(--border)] font-bold hover:bg-[var(--bg-card)] transition-colors">Cancel</Link>
                    <button @click="submit" :disabled="form.processing" class="px-6 py-3 bg-[var(--brand)] text-white rounded-xl shadow-lg shadow-[var(--brand-glow)] font-bold active:scale-95 transition-all">
                        Save Draft Audit
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
