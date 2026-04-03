<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome to Gusto" />
    
    <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-base)] selection:bg-[var(--brand)] selection:text-white overflow-hidden relative">
        <!-- Decorative Ambient Glows -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[var(--brand)]/10 blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-500/5 blur-[120px] rounded-full"></div>

        <!-- Navigation -->
        <nav class="relative z-10 flex justify-between items-center px-8 py-8 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <span class="text-4xl">🍴</span>
                <span class="text-3xl font-heading font-black tracking-tighter text-[var(--text-strong)]">Gusto<span class="text-[var(--brand)]">Prime</span></span>
            </div>
            
            <div v-if="canLogin" class="flex items-center gap-6">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="btn-primary px-8 py-3"
                >
                    Enter Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="text-sm font-bold text-[var(--text-strong)] hover:text-[var(--brand)] transition-colors uppercase tracking-widest"
                    >
                        Sign In
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn-primary px-8 py-3"
                    >
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative z-10 max-w-7xl mx-auto px-8 pt-20 pb-32 grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[var(--brand)]/10 border border-[var(--brand)]/20 text-[var(--brand)] text-xs font-black uppercase tracking-widest">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--brand)] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[var(--brand)]"></span>
                    </span>
                    The Future of Dining Operations
                </div>
                
                <h1 class="text-6xl md:text-8xl font-heading font-black text-[var(--text-strong)] leading-[0.9] tracking-tight">
                    Manage your <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--brand)] to-orange-400">Restaurant</span> <br/>
                    with Precision.
                </h1>
                
                <p class="text-xl text-[var(--text-muted)] max-w-lg font-medium leading-relaxed">
                    Gusto Prime is an enterprise-grade POS and Kitchen management system designed for speed, reliability, and premium guest experiences.
                </p>

                <div class="flex flex-wrap gap-4 pt-4">
                    <Link :href="route('login')" class="btn-primary px-10 py-5 text-lg shadow-xl shadow-orange-500/20">
                        Launch System
                    </Link>
                    <a href="#features" class="btn-secondary px-10 py-5 text-lg">
                        Explore Features
                    </a>
                </div>

                <!-- Trust signals -->
                <div class="pt-8 flex items-center gap-8 border-t border-[var(--border)] mt-12">
                    <div class="flex -space-x-3">
                        <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-[var(--bg-main)] bg-[var(--bg-surface)] flex items-center justify-center font-bold text-xs">
                            {{ ['J', 'M', 'A', 'K'][i-1] }}
                        </div>
                    </div>
                    <div class="text-sm font-bold text-[var(--text-muted)]">
                        Trusted by <span class="text-[var(--text-strong)]">500+</span> Premium Establishments
                    </div>
                </div>
            </div>

            <!-- Visual Part -->
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-tr from-[var(--brand)]/20 to-transparent blur-[100px] rounded-full scale-75 group-hover:scale-90 transition-transform duration-1000"></div>
                <div class="glass-panel p-2 relative overflow-hidden shadow-2xl rotate-2 group-hover:rotate-0 transition-all duration-700">
                    <div class="bg-[#141A23] rounded-xl overflow-hidden aspect-[4/3] relative">
                         <!-- Mock Interface Snippet -->
                         <div class="absolute inset-0 p-8 flex flex-col gap-6">
                            <div class="flex justify-between items-center">
                                <div class="w-32 h-4 bg-white/10 rounded-full"></div>
                                <div class="w-8 h-8 rounded-full bg-[var(--brand)]"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="i in 4" :key="i" class="h-24 bg-white/5 rounded-2xl border border-white/10 p-4 space-y-3">
                                    <div class="w-full h-2 bg-white/20 rounded-full"></div>
                                    <div class="w-1/2 h-2 bg-white/10 rounded-full"></div>
                                </div>
                            </div>
                            <div class="mt-auto p-4 bg-[var(--brand)] rounded-2xl flex justify-between items-center text-white font-black">
                                <span>TOTAL SALES</span>
                                <span>$14,280.00</span>
                            </div>
                         </div>
                         <!-- Overlays -->
                         <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>
                </div>
                <!-- Floating Badge -->
                <div class="absolute -bottom-6 -left-6 glass-card p-6 shadow-2xl animate-bounce-slow">
                    <div class="text-3xl font-heading font-black text-[var(--brand)]">99.9%</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Live Uptime</div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="relative z-10 border-t border-[var(--border)] py-12 px-8">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-sm font-bold text-[var(--text-muted)]">
                    © 2026 Gusto Prime Technologies. All rights reserved.
                </div>
                <div class="flex gap-8 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                    <a href="#" class="hover:text-[var(--text-strong)] transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-[var(--text-strong)] transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-[var(--text-strong)] transition-colors">Documentation</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.animate-bounce-slow {
    animation: bounce-slow 4s infinite ease-in-out;
}
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}
</style>
