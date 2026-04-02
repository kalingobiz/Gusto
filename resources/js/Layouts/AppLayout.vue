<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const sidebarOpen = ref(false);

const navItems = computed(() => {
    const role = user.value?.role;
    const items = [];

    if (role === 'admin' || role === 'cashier') {
        items.push({ label: 'Floor', href: route('tables.index'), icon: '🍽️' });
    }
    if (role === 'admin' || role === 'kitchen') {
        items.push({ label: 'Kitchen', href: route('kitchen.index'), icon: '👨‍🍳' });
    }
    if (role === 'admin') {
        items.push({ label: 'Dashboard', href: route('admin.dashboard'), icon: '📊' });
        items.push({ label: 'Menu', href: route('admin.menu.index'), icon: '📋' });
        items.push({ label: 'Ingredients', href: route('admin.ingredients.index'), icon: '🧂' });
        items.push({ label: 'BOM', href: route('admin.bom.index'), icon: '⚗️' });
        items.push({ label: 'Tables', href: route('admin.tables.admin'), icon: '🪑' });
        items.push({ label: 'Staff', href: route('admin.users.index'), icon: '👥' });
        items.push({ label: 'Reports', href: route('admin.reports.sales'), icon: '📈' });
    }

    return items;
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-56 bg-gray-900 text-white transition-transform duration-200 lg:relative lg:translate-x-0 lg:flex lg:flex-col"
        >
            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-700">
                <span class="text-xl font-bold tracking-wide text-amber-400">🍴 Gusto</span>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">✕</button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors"
                    :class="{ 'bg-amber-500 text-white': $page.url.startsWith(item.href.replace(window.location.origin, '')) }"
                >
                    <span>{{ item.icon }}</span>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-gray-700 p-4">
                <div class="text-xs text-gray-400">{{ user?.name }}</div>
                <div class="text-xs text-gray-500 capitalize">{{ user?.role }}</div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="mt-2 text-xs text-gray-400 hover:text-red-400"
                >Sign out</Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar (mobile) -->
            <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center lg:hidden">
                <button @click="sidebarOpen = true" class="text-gray-600 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="font-semibold text-gray-800">Gusto POS</span>
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
