<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const navLinks = [
    { label: 'Dashboard',    href: () => route('admin.dashboard'),      icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z' },
    { label: 'Floor',        href: () => route('tables.index'),          icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { label: 'Menu',         href: () => route('admin.menu.index'),      icon: 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z' },
    { label: 'Reports',      href: () => route('admin.reports.sales'),   icon: 'M2.25 18L9 11.25l4.5 4.5L21.75 7.5M21.75 14.25v-6.75h-6.75' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white flex flex-col">
        <header class="bg-gray-900 border-b border-gray-800 px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
            <!-- Branding -->
            <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-amber-400">🍴 Gusto — Kitchen</span>
            </div>

            <!-- Admin quick nav -->
            <nav v-if="isAdmin" class="flex items-center gap-1 flex-wrap">
                <Link
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href()"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-gray-400 hover:text-white hover:bg-white/10 transition-all"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon"/>
                    </svg>
                    {{ link.label }}
                </Link>
            </nav>

            <!-- User + signout -->
            <div class="flex items-center gap-3 text-sm text-gray-400">
                <span class="hidden sm:block text-xs">{{ user?.name }}</span>
                <Link :href="route('logout')" method="post" as="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all border border-red-500/20">
                    Sign out
                </Link>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-4">
            <slot />
        </main>
    </div>
</template>
