<script setup>
defineProps({
    tableNumber: String,
});
</script>

<template>
    <div class="min-h-screen bg-[var(--bg-main)] text-[var(--text-base)] transition-colors duration-300">
        <header class="bg-[var(--bg-card)] border-b border-[var(--border)] px-6 py-4 flex items-center justify-between sticky top-0 z-50 backdrop-blur-lg bg-opacity-80">
            <div class="flex items-center gap-2">
                <img src="/mylogo.png" alt="Logo" class="h-8 w-auto rounded-md shadow-sm" />
                <span class="text-xl font-black tracking-tighter text-[var(--text-strong)]">Gusto</span>
            </div>
            <div v-if="tableNumber" class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--brand)] text-white text-xs font-black uppercase tracking-wider shadow-lg shadow-[var(--brand-glow)]">
                <span class="opacity-70">{{ __('Table') }}</span>
                <span>{{ tableNumber }}</span>
            </div>
        </header>
        
        <main class="max-w-xl mx-auto pb-24 px-4 sm:px-6">
            <slot />
        </main>
    </div>
</template>
