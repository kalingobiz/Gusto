<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome to Gusto Prime" />

    <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-base)] selection:bg-[var(--brand)] selection:text-white overflow-hidden relative">
        <!-- Ambient Glows -->
        <div class="absolute -top-[15%] -left-[10%] w-[50%] h-[50%] bg-[var(--brand)]/8 blur-[160px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-[15%] -right-[10%] w-[45%] h-[45%] bg-blue-500/6 blur-[160px] rounded-full pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[60%] h-[40%] bg-purple-500/3 blur-[200px] rounded-full pointer-events-none"></div>

        <!-- Navigation -->
        <nav class="relative z-10 flex justify-between items-center px-8 py-8 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-white/5 border border-white/10 shadow-inner">
                    <img src="/mylogo.png" alt="Gusto Logo" class="h-9 w-auto drop-shadow-lg" />
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-heading font-black tracking-tight text-[var(--text-strong)] leading-none">Gusto</span>
                    <span class="text-[10px] font-black tracking-[0.2em] uppercase text-[var(--brand)] opacity-80 mt-0.5">Prime</span>
                </div>
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
        <main class="relative z-10 max-w-7xl mx-auto px-8 pt-16 pb-32 grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left: Copy -->
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
                    <Link :href="route('login')" class="btn-primary px-10 py-5 text-lg shadow-xl shadow-orange-500/20 rounded-2xl">
                        Launch System
                    </Link>
                    <a href="#features" class="btn-secondary px-10 py-5 text-lg rounded-2xl">
                        Explore Features
                    </a>
                </div>

                <!-- Trust signals -->
                <div class="pt-8 flex items-center gap-8 border-t border-[var(--border)] mt-12">
                    <div class="flex -space-x-3">
                        <div v-for="(letter, i) in ['J','M','A','K']" :key="i" class="w-10 h-10 rounded-full border-2 border-[var(--bg-main)] bg-gradient-to-tr from-[var(--brand)] to-orange-300 flex items-center justify-center font-black text-xs text-white shadow-md">
                            {{ letter }}
                        </div>
                    </div>
                    <div class="text-sm font-bold text-[var(--text-muted)]">
                        Trusted by <span class="text-[var(--text-strong)]">500+</span> Premium Establishments
                    </div>
                </div>
            </div>

            <!-- Right: Real-looking Gusto UI Mockup -->
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-tr from-[var(--brand)]/20 to-transparent blur-[100px] rounded-full scale-75 group-hover:scale-90 transition-transform duration-1000"></div>

                <div class="glass-panel p-3 relative overflow-hidden shadow-2xl rotate-2 group-hover:rotate-0 transition-all duration-700">
                    <!-- Mock App Shell -->
                    <div class="bg-[#141A23] rounded-xl overflow-hidden" style="aspect-ratio: 4/3;">
                        <!-- Mock Sidebar -->
                        <div class="flex h-full">
                            <div class="w-14 bg-[#0B0E14] flex flex-col items-center py-4 gap-3 border-r border-white/5">
                                <div class="w-8 h-8 rounded-xl bg-orange-500 flex items-center justify-center mb-2">
                                    <div class="w-4 h-4 bg-white/80 rounded-sm"></div>
                                </div>
                                <div v-for="i in 5" :key="i" class="w-8 h-8 rounded-xl" :class="i === 1 ? 'bg-orange-500/20 border border-orange-500/40' : 'bg-white/5'"></div>
                            </div>

                            <!-- Mock Content -->
                            <div class="flex-1 p-4 flex flex-col gap-3">
                                <!-- Header bar -->
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex flex-col gap-1">
                                        <div class="w-24 h-2.5 bg-white/80 rounded-full"></div>
                                        <div class="w-36 h-1.5 bg-white/20 rounded-full"></div>
                                    </div>
                                    <div class="flex gap-2">
                                        <div class="w-16 h-5 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center gap-1 px-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                            <div class="w-6 h-1 bg-emerald-400/60 rounded-full"></div>
                                        </div>
                                        <div class="w-10 h-5 rounded-lg bg-white/10 flex items-center justify-center">
                                            <div class="w-6 h-1 bg-white/40 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats row -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div v-for="(stat, i) in [{color:'emerald',val:'14,280'},{color:'orange',val:'47'},{color:'blue',val:'12'}]" :key="i"
                                        class="rounded-xl p-2.5 border"
                                        :class="`bg-${stat.color}-500/5 border-${stat.color}-500/20`">
                                        <div class="w-4 h-4 rounded-lg mb-1.5" :class="`bg-${stat.color}-500/20`"></div>
                                        <div class="w-8 h-1 rounded-full mb-1" :class="`bg-${stat.color}-500/40`"></div>
                                        <div class="text-white font-black text-xs">{{ stat.val }}</div>
                                    </div>
                                </div>

                                <!-- Mock order list -->
                                <div class="flex-1 rounded-xl bg-white/3 border border-white/8 overflow-hidden">
                                    <div class="px-3 py-2 border-b border-white/8 flex items-center justify-between">
                                        <div class="w-20 h-1.5 bg-white/40 rounded-full"></div>
                                        <div class="w-10 h-1 bg-orange-500/60 rounded-full"></div>
                                    </div>
                                    <div v-for="j in 4" :key="j" class="px-3 py-2 border-b border-white/5 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-white/8 border border-white/10"></div>
                                            <div class="space-y-1">
                                                <div class="h-1.5 bg-white/30 rounded-full" :style="`width:${40 + j*8}px`"></div>
                                                <div class="h-1 bg-white/15 rounded-full" :style="`width:${30 + j*5}px`"></div>
                                            </div>
                                        </div>
                                        <div class="px-2 py-0.5 rounded-md text-[8px] font-black" :class="j===1?'bg-emerald-500 text-white':j===2?'bg-amber-500/20 text-amber-400':j===3?'bg-blue-500/20 text-blue-400':'bg-white/10 text-white/40'">
                                            {{ ['PAID','COOKING','READY','DRAFT'][j-1] }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Bottom total bar -->
                                <div class="p-3 rounded-xl flex justify-between items-center text-white font-black text-xs" style="background: var(--gradient-brand);">
                                    <span>TODAY'S REVENUE</span>
                                    <span>14,280.00 Birr</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating uptime badge -->
                <div class="absolute -bottom-6 -left-6 glass-card p-5 shadow-2xl animate-bounce-slow">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <div class="text-2xl font-heading font-black text-[var(--brand)]">99.9%</div>
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Live Uptime</div>
                </div>

                <!-- Floating KDS badge -->
                <div class="absolute -top-4 -right-4 glass-card px-4 py-3 shadow-2xl">
                    <div class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Kitchen Display</div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[var(--brand)] animate-pulse"></span>
                        <span class="text-sm font-black text-[var(--text-strong)]">3 Active Orders</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Feature pills -->
        <section id="features" class="relative z-10 max-w-7xl mx-auto px-8 pb-24">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="feat in [
                    { icon: '🖥️', title: 'Smart POS', desc: 'Touch-optimized order taking' },
                    { icon: '👨‍🍳', title: 'Live KDS', desc: 'Real-time kitchen display' },
                    { icon: '📊', title: 'Analytics', desc: 'Revenue & item reports' },
                    { icon: '📦', title: 'Inventory', desc: 'BOM-based stock tracking' },
                ]" :key="feat.title" class="glass-card p-5 hover:-translate-y-1 transition-transform duration-300">
                    <div class="text-2xl mb-3">{{ feat.icon }}</div>
                    <div class="font-black text-sm text-[var(--text-strong)] mb-1">{{ feat.title }}</div>
                    <div class="text-[11px] text-[var(--text-muted)] font-medium">{{ feat.desc }}</div>
                </div>
            </div>
        </section>

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
