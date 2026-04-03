<template>
    <div class="h-screen w-screen flex flex-col overflow-hidden bg-[#0B0E14] text-slate-300 dark theme-forced-dark">
        <!-- KDS Header -->
        <header class="flex-shrink-0 h-20 bg-[#141A23] border-b border-[#2A3441] px-6 flex items-center justify-between shadow-md relative z-10">
            <div class="flex items-center gap-6">
                <!-- Branding -->
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🔥</span>
                    <h1 class="text-2xl font-heading font-black tracking-widest text-white shadow-orange-500/50 drop-shadow-md">KDS</h1>
                </div>
                <div class="px-4 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-bold uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Setup
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <slot name="header-actions" />
                <div class="text-4xl font-heading font-black text-white tracking-widest bg-[#0B0E14] px-4 py-2 rounded-xl border border-[#2A3441] shadow-inner">
                    {{ currentTime }}
                </div>
                <!-- Quick Exit for Admin -->
                <Link :href="route('admin.dashboard')" class="btn-icon w-12 h-12 text-slate-400 hover:text-white border-[#2A3441] hover:border-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </Link>
            </div>
        </header>

        <!-- Horizontal Scroll Area -->
        <main class="flex-1 overflow-x-auto overflow-y-hidden p-6 custom-scrollbar relative z-0">
            <div class="h-full flex gap-6 items-start pb-4">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const currentTime = ref('00:00:00');
let timer;

onMounted(() => {
    // Force dark mode locally for KDS to reduce eye strain
    document.documentElement.classList.add('dark');
    
    const updateTime = () => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };
    
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<style scoped>
/* Ensure scrollbars in dark mode look good */
.custom-scrollbar::-webkit-scrollbar {
    height: 12px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0B0E14;
    border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #2A3441;
    border-radius: 8px;
    border: 3px solid #0B0E14;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
