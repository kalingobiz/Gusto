<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative min-h-screen flex items-center justify-center bg-[#0B0E14] overflow-hidden"
    >
        <!-- Background with Overlay -->
        <div 
            class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-1000"
            style="background-image: url('/images/auth-bg.png');"
        ></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/40 to-transparent"></div>

        <!-- Floating Elements for Depth -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-orange-500/10 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-blue-500/10 blur-[120px] rounded-full"></div>

        <div class="relative z-10 w-full max-w-lg px-8 py-12 flex flex-col items-center">
            <!-- Glassmorphic Card -->
            <div class="w-full bg-[#141A23]/60 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] shadow-[0_32px_64px_-16px_rgba(0,0,0,0.6)] overflow-hidden">
                <div class="px-10 py-12">
                    <div class="flex flex-col items-center mb-10">
                        <Link href="/" class="group flex flex-col items-center">
                            <div class="mb-4 p-4 rounded-2xl bg-white/5 border border-white/10 group-hover:border-orange-500/50 transition-all duration-500 shadow-inner">
                                <ApplicationLogo class="h-16 w-auto drop-shadow-[0_0_15px_rgba(255,165,0,0.4)]" />
                            </div>
                            <h2 class="text-3xl font-heading font-black text-white tracking-widest uppercase">
                                Gusto<span class="text-orange-500">Prime</span>
                            </h2>
                            <div class="w-12 h-1 bg-orange-500 rounded-full mt-2 opacity-60 group-hover:w-20 transition-all duration-700"></div>
                        </Link>
                    </div>

                    <slot />
                </div>
            </div>

            <!-- Footer for Auth -->
            <footer class="mt-12 text-[10px] uppercase tracking-[0.3em] font-black text-white/30 text-center">
                &copy; 2026 Gusto Operations Management System
            </footer>
        </div>
    </div>
</template>

<style>
/* Custom animations if needed */
.font-heading {
    font-family: 'Outfit', sans-serif;
}
</style>
