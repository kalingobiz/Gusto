<template>
    <POSLayout>
        <div class="grid grid-cols-3 gap-6">
            <!-- Mock Menu Items -->
            <div v-for="i in 9" :key="i" class="glass-card flex flex-col items-center justify-center p-6 btn-haptic cursor-pointer">
                <div class="w-20 h-20 bg-[var(--bg-surface)] rounded-full mb-4 flex items-center justify-center text-4xl shadow-inner">
                    🍔
                </div>
                <h3 class="text-xl font-heading font-semibold text-[var(--text-strong)] text-center">Gusto Burger {{i}}</h3>
                <p class="text-[var(--brand)] font-bold mt-2">$24.00</p>
            </div>
        </div>

        <template #cart>
            <div class="flex-1 flex flex-col p-6">
                <h2 class="text-2xl font-heading font-bold mb-6 pb-4 border-b border-[var(--border)]">Order #1042</h2>
                <div class="flex-1 overflow-y-auto space-y-4">
                    <div v-for="i in 3" :key="i" class="flex justify-between items-center bg-[var(--bg-surface)] p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-bold">1</span>
                            <span class="font-semibold">Gusto Burger {{i}}</span>
                        </div>
                        <span class="font-bold">$24.00</span>
                    </div>
                </div>
                <div class="pt-6 mt-6 border-t border-[var(--border)] space-y-4">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-[var(--brand)] text-2xl">$72.00</span>
                    </div>
                    <button class="w-full btn-primary py-4 text-xl tracking-wide uppercase">Send To Kitchen</button>
                    <button class="w-full btn-secondary py-4 text-xl tracking-wide uppercase">Pay Now</button>
                </div>
            </div>
        </template>
    </POSLayout>
</template>

<script setup>
import POSLayout from '@/Layouts/POSLayout.vue';
</script>
