<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Terminal Access" />

        <div class="mb-8 text-center">
            <h1 class="text-xl font-bold text-white uppercase tracking-widest mb-1">Terminal Authentication</h1>
            <p class="text-xs text-white/40 font-black uppercase tracking-wider">Please authorize to continue operations</p>
        </div>

        <div v-if="status" class="mb-6 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
                <InputLabel for="email" value="Operational Email" class="text-xs font-black uppercase tracking-widest text-white/50 ml-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full bg-white/5 border-white/10 text-white focus:border-orange-500/50 focus:ring-orange-500/20 rounded-2xl h-14 px-6 transition-all duration-300"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@gustoprime.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between ml-1">
                    <InputLabel for="password" value="System Password" class="text-xs font-black uppercase tracking-widest text-white/50" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-black uppercase tracking-widest text-orange-500/70 hover:text-orange-400 transition-colors"
                    >
                        Forgot Code?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full bg-white/5 border-white/10 text-white focus:border-orange-500/50 focus:ring-orange-500/20 rounded-2xl h-14 px-6 transition-all duration-300"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block">
                <label class="flex items-center group cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="bg-white/5 border-white/10 text-orange-500 rounded focus:ring-orange-500/20" />
                    <span class="ms-3 text-xs font-black text-white/40 group-hover:text-white/60 transition-colors uppercase tracking-widest"
                        >Maintain terminal session</span
                    >
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full h-16 rounded-2xl bg-orange-500 hover:bg-orange-400 text-white shadow-[0_15px_30px_-10px_rgba(255,165,0,0.5)] active:scale-[0.98] transition-all duration-300 flex justify-center items-center gap-3 group"
                    :class="{ 'opacity-50 grayscale pointer-events-none': form.processing }"
                    :disabled="form.processing"
                >
                    <span class="font-heading font-black text-lg uppercase tracking-[0.2em]">Authorize Access</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

