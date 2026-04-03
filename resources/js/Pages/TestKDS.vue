<template>
    <KDSLayout>
        <div v-for="order in 4" :key="order" class="w-96 flex-shrink-0 bg-[#161B22] rounded-2xl border border-[#30363D] shadow-2xl flex flex-col h-full overflow-hidden">
            <div class="px-6 py-4 border-b border-[#30363D] flex items-center justify-between" :class="order === 1 ? 'bg-red-500/10 border-red-500/30' : 'bg-[#1C2128]'">
                <div>
                    <h2 class="text-xl font-heading font-black text-white">#104{{ order }}</h2>
                    <p class="text-slate-400 text-sm font-bold">Table {{ order * 2 }}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold font-heading" :class="order === 1 ? 'text-red-400 animate-pulse' : 'text-emerald-400'">
                        {{ order === 1 ? '15:42' : '0' + order + ':12' }}
                    </div>
                </div>
            </div>
            
            <div class="p-6 flex-1 overflow-y-auto space-y-4">
                <div class="flex items-start gap-4" v-for="item in 3" :key="item">
                    <button class="w-8 h-8 rounded-lg border-2 border-[#475569] flex-shrink-0 flex items-center justify-center hover:border-emerald-500 hover:bg-emerald-500/20 transition-colors"></button>
                    <div>
                        <div class="text-lg font-bold text-white leading-tight">1x Gusto Burger {{ item }}</div>
                        <div class="text-sm font-semibold text-amber-500/80 mt-1 uppercase tracking-wide px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 inline-block">No Onions</div>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-[#30363D] bg-[#161B22]">
                <button class="w-full btn-haptic py-4 rounded-xl text-lg font-bold uppercase tracking-widest text-white shadow-lg" :class="order === 1 ? 'bg-red-600 hover:bg-red-500' : 'bg-emerald-600 hover:bg-emerald-500'">
                    Bump Order
                </button>
            </div>
        </div>
    </KDSLayout>
</template>

<script setup>
import KDSLayout from '@/Layouts/KDSLayout.vue';
</script>
