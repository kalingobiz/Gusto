<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const sidebarOpen = ref(false);
const isDark = ref(localStorage.getItem('theme') === 'dark');

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
if (isDark.value) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

const navItems = computed(() => {
    const role = user.value?.role;
    const items = [];

    // Using explicit SVG paths for consistency
    const icons = {
        floor: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
        kitchen: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />',
        dashboard: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
        menu: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />',
        ingredients: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />',
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
    <div class="min-h-screen flex text-[var(--text-base)]">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[var(--bg-card)] border-r border-[var(--border)] transition-transform duration-300 lg:relative lg:translate-x-0 lg:flex lg:flex-col shadow-xl lg:shadow-none"
        >
            <div class="flex items-center justify-between px-6 py-6">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🍴</span>
                    <span class="text-xl font-black tracking-tight text-[var(--text-strong)]">Gusto</span>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-[var(--text-muted)] hover:text-[var(--text-strong)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 group relative overflow-hidden"
                    :class="isActive(item.href) 
                        ? 'bg-[var(--brand)] text-white shadow-lg shadow-[var(--brand-glow)]' 
                        : 'text-[var(--text-muted)] hover:bg-[var(--bg-surface)] hover:text-[var(--text-strong)]'"
                >
                    <div 
                        class="w-5 h-5 flex items-center justify-center transition-all duration-300 group-hover:scale-110"
                        v-html="`<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'>${item.icon}</svg>`"
                    ></div>
                    <span class="relative z-10">{{ item.label }}</span>
                    <div v-if="isActive(item.href)" class="absolute right-0 top-0 bottom-0 w-1 bg-white/20"></div>
                </Link>
            </nav>

            <div class="p-4 space-y-4">
                <!-- Theme Toggle -->
                <button 
                    @click="toggleTheme" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-strong)] hover:border-[var(--brand)] transition-all"
                >
                    <span class="text-sm font-medium">{{ isDark ? 'Night Mode' : 'Day Mode' }}</span>
                    <span class="text-lg">{{ isDark ? '🌙' : '☀️' }}</span>
                </button>

                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 rounded-full bg-[var(--brand)] flex items-center justify-center text-white font-bold">
                        {{ user?.name?.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-[var(--text-strong)] truncate">{{ user?.name }}</div>
                        <div class="text-xs text-[var(--text-muted)] capitalize">{{ user?.role }}</div>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full mt-4 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-red-200/50 text-red-600 text-xs font-bold hover:bg-red-50 dark:border-red-900/20 dark:hover:bg-red-900/10 transition-all duration-300 active:scale-95 group"
                >
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign out
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 bg-[var(--bg-main)]">
            <!-- Top bar -->
            <header class="bg-[var(--bg-card)] border-b border-[var(--border)] px-6 py-4 flex items-center justify-between sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-[var(--text-muted)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h2 class="text-lg font-bold text-[var(--text-strong)] hidden lg:block">Gusto POS Dashboard</h2>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live System Connected
                    </div>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" class="px-6 pt-4">
                <div
                    v-if="flash.success"
                    class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash.error"
                    class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm"
                >
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>

        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        />
    </div>
</template>
