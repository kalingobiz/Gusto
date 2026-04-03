<template>
    <div class="h-screen w-screen flex overflow-hidden bg-[var(--bg-main)] text-[var(--text-base)]">
        <!-- Main Content Area (Left 70-75%) -->
        <main class="flex-1 flex flex-col h-full overflow-hidden relative">
            <!-- Header for POS -->
            <header class="flex-shrink-0 h-20 bg-[var(--bg-card)]/80 backdrop-blur-xl border-b border-[var(--border)] px-6 flex items-center justify-between z-10 shadow-sm relative">
                <div class="flex items-center gap-4">
                    <Link :href="route('admin.dashboard')" class="btn-icon w-12 h-12">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </Link>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">🍴</span>
                        <h1 class="text-2xl font-heading font-black tracking-tight text-[var(--text-strong)]">Gusto Prime</h1>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Session / User info -->
                    <slot name="header-actions">
                        <div class="px-4 py-2 rounded-xl bg-[var(--info)]/10 border border-[var(--info)]/20 text-[var(--info)] font-bold font-heading">
                            Terminal 1
                        </div>
                    </slot>
                </div>
            </header>
            
            <!-- Dynamic Content (Menu Grid/Categories) -->
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar relative z-0">
                <slot />
            </div>
        </main>

        <!-- Cart / Ticket Area (Right 25-30%) -->
        <aside class="w-[400px] flex-shrink-0 bg-[var(--bg-card)] border-l border-[var(--border)] shadow-2xl z-20 flex flex-col relative h-full">
            <slot name="cart" />
        </aside>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
