<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const sidebarOpen = ref(false);
const isDark = ref(localStorage.getItem('theme') !== 'light'); // Default to dark for 'Prime' feel

// Live clock
const clock = ref('');
let clockTimer;
function updateClock() {
    const now = new Date();
    clock.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
if (typeof window !== 'undefined') {
    updateClock();
    clockTimer = setInterval(updateClock, 30000);
}

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Initialize theme on mount
onMounted(() => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});

onUnmounted(() => clearInterval(clockTimer));

const navItems = computed(() => {
    const role = user.value?.role;
    const items = [];

    const icons = {
        floor: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
        kitchen: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />',
        dashboard: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
        menu: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />',
        ingredients: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />',
        audits: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        bom: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-7.5 0V3.375m0 17.25V21M7.03 7.03L6.108 6.107m11.784 11.784l-.922-.923m0-11.784l.922-.923M6.108 17.893l.922-.923" />',
        tables: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M19.5 3v11.25a2.25 2.25 0 01-2.25 2.25H15m-6.75 0v3a2.25 2.25 0 002.25 2.25h3a2.25 2.25 0 002.25-2.25v-3M3.75 7.5h16.5M5.625 7.5v6.75m12.75-6.75v6.75" />',
        staff: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.998 5.998 0 00-5.484-5.934c-.753-.059-1.516-.059-2.27 0A5.998 5.998 0 006 18.72m12 0a9 9 0 00-18 0" />',
        reports: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18L9 11.25l4.5 4.5L21.75 7.5M21.75 14.25v-6.75h-6.75" />'
    };

    if (role === 'admin' || role === 'cashier') {
        items.push({ label: 'Floor', href: route('tables.index'), icon: icons.floor });
    }
    if (role === 'admin' || role === 'kitchen') {
        items.push({ label: 'Kitchen', href: route('kitchen.index'), icon: icons.kitchen });
    }
    if (role === 'admin') {
        items.push({ label: 'Dashboard', href: route('admin.dashboard'), icon: icons.dashboard });
        items.push({ label: 'Menu', href: route('admin.menu.index'), icon: icons.menu });
        items.push({ label: 'Ingredients', href: route('admin.ingredients.index'), icon: icons.ingredients });
        items.push({ label: 'Audits', href: route('admin.stocktakes.index'), icon: icons.audits });
        items.push({ label: 'BOM', href: route('admin.bom.index'), icon: icons.bom });
        items.push({ label: 'Tables', href: route('admin.tables.admin'), icon: icons.tables });
        items.push({ label: 'Staff', href: route('admin.users.index'), icon: icons.staff });
        items.push({ label: 'Reports', href: route('admin.reports.sales'), icon: icons.reports });
    }

    return items;
});

const isActive = (href) => {
    try {
        const path = new URL(href).pathname;
        return path === '/' ? page.url === '/' : page.url.startsWith(path);
    } catch (e) {
        return false;
    }
};
</script>

<template>
    <div class="min-h-screen flex text-[var(--text-base)] font-sans relative overflow-hidden bg-[var(--bg-main)]">
        <!-- Persistent Ambient Glows -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-[var(--brand)]/5 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-blue-500/5 blur-[120px] rounded-full pointer-events-none"></div>

        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[var(--bg-card)]/40 backdrop-blur-2xl border-r border-[var(--border)] transition-all duration-500 lg:relative lg:translate-x-0 lg:flex lg:flex-col shadow-[20px_0_40px_rgba(0,0,0,0.1)]"
        >
            <div class="flex items-center justify-between px-7 py-8">
                <div class="flex items-center gap-3 group">
                    <div class="p-2 rounded-xl bg-white/5 border border-white/10 shadow-inner group-hover:border-orange-500/50 transition-colors">
                        <img src="/mylogo.png" alt="Gusto Logo" class="h-8 w-auto drop-shadow-lg" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-heading font-black tracking-tight text-[var(--text-strong)] leading-none">Gusto</span>
                        <span class="text-[10px] font-black tracking-[0.2em] uppercase text-orange-500 opacity-80 mt-1">Prime</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-[var(--text-muted)] hover:text-[var(--text-strong)] btn-haptic p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-4 space-y-1.5 custom-scrollbar">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3.5 px-5 py-3 rounded-2xl text-sm font-bold transition-all duration-300 group relative overflow-hidden"
                    :class="isActive(item.href) 
                        ? 'bg-gradient-to-r from-[var(--brand)] to-orange-400 text-white shadow-xl shadow-[var(--brand-glow)] active-nav-glow' 
                        : 'text-[var(--text-muted)] hover:bg-white/5 hover:text-[var(--text-strong)] border border-transparent hover:border-white/10'"
                >
                    <div 
                        class="w-5 h-5 flex items-center justify-center transition-all duration-500 group-hover:scale-125"
                        :class="isActive(item.href) ? 'scale-110' : ''"
                        v-html="`<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2.5'>${item.icon}</svg>`"
                    ></div>
                    <span class="relative z-10 tracking-wide">{{ __(item.label) }}</span>
                    <div v-if="isActive(item.href)" class="absolute inset-0 bg-white opacity-10 animate-pulse"></div>
                </Link>
            </nav>

            <div class="p-4 mt-auto space-y-4 border-t border-[var(--border)]/50">
                <!-- Theme Toggle -->
                <button 
                    @click="toggleTheme" 
                    class="w-full flex items-center justify-between px-5 py-3.5 rounded-2xl bg-white/5 border border-white/5 text-[var(--text-strong)] hover:border-orange-500/30 hover:bg-orange-500/5 btn-haptic transition-all duration-300 group"
                >
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-[var(--text-strong)]">{{ isDark ? __('System Night') : __('System Day') }}</span>
                    <span class="text-xl transition-transform duration-500 group-hover:rotate-12">{{ isDark ? '🌙' : '☀️' }}</span>
                </button>

                <div class="p-1 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] flex gap-1 mb-2">
                    <Link :href="route('language.switch', 'en')" class="flex-1 py-2 text-center rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="$page.props.locale === 'en' ? 'bg-[var(--brand)] text-white shadow-md' : 'text-[var(--text-muted)] hover:bg-[var(--bg-card)]'">EN</Link>
                    <Link :href="route('language.switch', 'am')" class="flex-1 py-2 text-center rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="$page.props.locale === 'am' ? 'bg-[var(--brand)] text-white shadow-md' : 'text-[var(--text-muted)] hover:bg-[var(--bg-card)]'">አማ</Link>
                    <Link :href="route('language.switch', 'om')" class="flex-1 py-2 text-center rounded-lg text-[10px] font-black uppercase tracking-widest transition-all" :class="$page.props.locale === 'om' ? 'bg-[var(--brand)] text-white shadow-md' : 'text-[var(--text-muted)] hover:bg-[var(--bg-card)]'">OM</Link>
                </div>

                <div class="flex items-center gap-3.5 px-3 py-3 rounded-2xl bg-white/5 border border-white/5 backdrop-blur-sm">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-[var(--brand)] to-orange-300 flex items-center justify-center text-white font-black text-lg shadow-lg">
                        {{ user?.name?.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-black text-[var(--text-strong)] truncate tracking-wide">{{ user?.name }}</div>
                        <div class="text-[10px] text-orange-500 font-bold uppercase tracking-widest">{{ __(user?.role) }} {{ __('mode') }}</div>
                    </div>
                </div>
                
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center justify-center gap-2.5 px-4 py-3.5 rounded-2xl border border-red-500/10 text-red-500/60 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-red-500/10 hover:text-red-500 hover:border-red-500/30 btn-haptic transition-all duration-500 group"
                >
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    {{ __('Logout') }}
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 relative z-10 transition-all duration-500">
            <!-- Top bar -->
            <header class="bg-[var(--bg-card)]/40 backdrop-blur-2xl border-b border-[var(--border)] px-8 py-5 flex items-center justify-between sticky top-0 z-40">
                <div class="flex items-center gap-6">
                    <button @click="sidebarOpen = true" class="lg:hidden btn-icon w-12 h-12 rounded-xl !bg-white/5 hover:!bg-orange-500/10 hover:!border-orange-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <!-- Dynamic page title slot -->
                    <div class="hidden lg:block">
                        <slot name="header">
                            <h2 class="text-2xl font-heading font-black text-[var(--text-strong)] tracking-tight">{{ __('System') }} <span class="text-orange-500">{{ __('Overview') }}</span></h2>
                        </slot>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Low stock alert -->
                    <Link
                        v-if="page.props.lowStockCount > 0"
                        :href="route('admin.ingredients.index')"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-[0.15em] animate-pulse shadow-[0_0_20px_rgba(239,68,68,0.15)]"
                    >
                        <span class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]"></span>
                        {{ page.props.lowStockCount }} {{ __('Shortages') }}
                    </Link>

                    <!-- Live indicator -->
                    <div class="hidden sm:flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-[10px] font-black uppercase tracking-[0.15em] shadow-[0_0_20px_rgba(16,185,129,0.1)]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        {{ __('Active Terminal') }}
                    </div>

                    <!-- Clock -->
                    <div class="hidden md:flex items-center px-5 py-2.5 rounded-2xl bg-white/5 border border-white/5 text-xs font-black text-[var(--text-strong)] font-mono tracking-[0.2em] shadow-inner">
                        {{ clock }}
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" class="px-8 pt-6">
                <div
                    v-if="flash.success"
                    class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-6 py-4 rounded-2xl text-sm font-bold shadow-lg flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash.error"
                    class="bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl text-sm font-bold shadow-lg flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-500"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-8 overflow-y-auto custom-scrollbar">
                <div class="max-w-[1600px] mx-auto h-full">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-500"
        />
    </div>
</template>

<style>
.active-nav-glow::after {
    content: '';
    position: absolute;
    inset: 0;
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.2);
    pointer-events: none;
}

.font-heading {
    font-family: 'Outfit', sans-serif;
}
</style>
