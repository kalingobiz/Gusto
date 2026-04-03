<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="btn-danger inline-flex items-center px-4 py-2 text-xs font-semibold uppercase tracking-widest disabled:opacity-25 transition"
    >
        <slot />
    </button>
</template>
