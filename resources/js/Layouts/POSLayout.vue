<template>
    <div class="h-screen w-screen flex overflow-hidden bg-[var(--bg-main)] text-[var(--text-base)] relative">
        <!-- Persistent Ambient Glows -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[var(--brand)]/5 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-500/5 blur-[120px] rounded-full pointer-events-none"></div>

        <!-- Main Content Area (Left 70-75%) -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative z-10">
            <!-- Header for POS -->
            <header class="flex-shrink-0 h-24 bg-[var(--bg-card)]/40 backdrop-blur-2xl border-b border-[var(--border)] px-8 flex items-center justify-between z-10 shadow-sm relative">
                <div class="flex items-center gap-6">
                    <Link :href="route('admin.dashboard')" class="btn-icon w-14 h-14 rounded-2xl bg-white/5 border-white/10 hover:border-orange-500/30 hover:bg-orange-500/5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </Link>
                    <div class="flex items-center gap-4 group">
                        <div class="p-2.5 rounded-2xl bg-white/5 border border-white/10 shadow-inner group-hover:border-orange-500/50 transition-colors">
                            <img src="/mylogo.png" alt="Logo" class="h-10 w-auto drop-shadow-xl" />
                        </div>
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-heading font-black tracking-tight text-[var(--text-strong)] leading-none">Gusto</h1>
                            <span class="text-[10px] font-black tracking-[0.3em] uppercase text-orange-500 opacity-80 mt-1.5">Terminal Prime</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Session / User info -->
                    <slot name="header-actions">
                        <div class="px-6 py-3 rounded-2xl bg-[var(--info)]/5 border border-[var(--info)]/20 text-[var(--info)] text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-500/5">
                            Terminal 01
                        </div>
                    </slot>
                </div>
            </header>
            
            <!-- Dynamic Content (Menu Grid/Categories) -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar relative z-0">
                <slot />
            </div>
        </main>

        <!-- Cart / Ticket Area (Right 25-30%) -->
        <aside class="w-[450px] flex-shrink-0 bg-[var(--bg-card)]/40 backdrop-blur-3xl border-l border-[var(--border)] shadow-[-20px_0_40px_rgba(0,0,0,0.1)] z-20 flex flex-col relative h-full">
            <slot name="cart" />
        </aside>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

