<template>
    <div class="h-screen w-screen flex flex-col overflow-hidden bg-[#0B0E14] text-slate-300 dark theme-forced-dark font-sans relative">
        <!-- Persistent Ambient Glows -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-orange-500/10 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-500/5 blur-[120px] rounded-full pointer-events-none"></div>

        <!-- KDS Header -->
        <header class="flex-shrink-0 h-24 bg-[#141A23]/60 backdrop-blur-2xl border-b border-[#2A3441] px-8 flex items-center justify-between shadow-2xl relative z-10">
            <div class="flex items-center gap-8">
                <!-- Branding -->
                <div class="flex items-center gap-4 group">
                    <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 shadow-inner group-hover:border-orange-500/50 transition-colors">
                        <img src="/mylogo.png" alt="Logo" class="h-10 w-auto drop-shadow-xl" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-2xl font-heading font-black tracking-tight text-white leading-none">Gusto</h1>
                        <span class="text-[10px] font-black tracking-[0.3em] uppercase text-orange-500 opacity-80 mt-1.5">Kitchen Authority</span>
                    </div>
                </div>
                <div class="px-5 py-2 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-black uppercase tracking-[0.2em] flex items-center gap-2.5 shadow-lg shadow-emerald-500/5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    Live Dispatch
                </div>
            </div>
            
            <div class="flex items-center gap-8">
                <slot name="header-actions" />
                <div class="text-5xl font-heading font-black text-white tracking-widest bg-black/40 px-6 py-3 rounded-2xl border border-white/10 shadow-inner tabular-nums drop-shadow-lg">
                    {{ currentTime }}
                </div>
                <!-- Quick Exit for Admin -->
                <Link :href="route('admin.dashboard')" class="btn-icon w-14 h-14 !bg-white/5 border-white/10 hover:!border-orange-500/30 hover:!bg-orange-500/5 transition-all text-slate-400 hover:text-white rounded-2xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </Link>
            </div>
        </header>

        <!-- Horizontal Scroll Area -->
        <main class="flex-1 overflow-x-auto overflow-y-hidden p-8 custom-scrollbar relative z-0">
            <div class="h-full flex gap-8 items-start pb-4">
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
    document.documentElement.classList.add('dark');
    
    const updateTime = () => {
        const now = new Date();
        currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\s[AP]M$/, '');
    };
    
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<style scoped>
.font-heading {
    font-family: 'Outfit', sans-serif;
}
.custom-scrollbar::-webkit-scrollbar {
    height: 12px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.2);
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

