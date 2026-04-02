<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="min-h-screen bg-gray-950 text-white flex flex-col">
        <header class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between">
            <span class="text-lg font-bold text-amber-400">🍴 Gusto — Kitchen Display</span>
            <div class="flex items-center gap-4 text-sm text-gray-400">
                <span class="hidden sm:block">{{ user?.name }}</span>
                <Link :href="route('logout')" method="post" as="button" class="hover:text-red-400">Sign out</Link>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-4">
            <slot />
        </main>
    </div>
</template>
