<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
    modelValue: String,
    routeName: String,
    placeholder: { type: String, default: 'Search...' },
});

const emit = defineEmits(['update:modelValue']);
const search = ref(props.modelValue);

const performSearch = debounce((value) => {
    router.get(route(props.routeName), { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch(search, (newValue) => {
    emit('update:modelValue', newValue);
    performSearch(newValue);
});
</script>

<template>
    <div class="relative group max-w-sm w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--text-muted)] group-focus-within:text-[var(--brand)] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            v-model="search"
            type="text"
            :placeholder="placeholder"
            class="input-premium pl-12 shadow-sm"
        />
    </div>
</template>
