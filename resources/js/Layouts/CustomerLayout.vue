<script setup>
defineProps({
    tableNumber: String,
});
</script>

<template>
    <div class="min-h-screen bg-amber-50">
        <header class="bg-amber-500 text-white px-4 py-3 flex items-center gap-2 sticky top-0 z-10 shadow-sm">
            <span class="text-lg font-bold">🍴 Gusto</span>
            <span v-if="tableNumber" class="ml-auto text-sm bg-amber-600 px-3 py-1 rounded-full">
                Table {{ tableNumber }}
            </span>
        </header>
        <main class="max-w-lg mx-auto pb-32">
            <slot />
        </main>
    </div>
</template>
