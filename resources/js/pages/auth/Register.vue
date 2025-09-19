<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { User, Mail, Lock, LogIn } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="relative w-full min-h-screen overflow-hidden bg-gray-900 text-gray-100 font-sans flex items-center justify-center p-4">
        <!-- Background effects from Welcome.vue -->
        <div class="absolute top-0 left-0 w-full h-full bg-grid-gray-800/[0.2] z-0">
            <div class="absolute pointer-events-none inset-0 flex items-center justify-center bg-gray-900 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]"></div>
        </div>
        <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] rounded-full bg-lime-500/10 blur-3xl z-0 animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] rounded-full bg-violet-500/10 blur-3xl z-0 animate-pulse-slow-delay"></div>

        <!-- Register Card -->
        <div class="relative z-10 w-full max-w-md p-8 space-y-8 bg-gray-900/50 backdrop-blur-sm border border-white/10 rounded-2xl shadow-2xl">
            <div class="text-center">
                 <Link href="/" class="flex items-center justify-center gap-2 mb-6">
                    <svg class="h-8 w-8 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m15 3H6m12 0a1.5 1.5 0 01-1.5 1.5H9a1.5 1.5 0 01-1.5-1.5m1.5-12a3 3 0 00-3-3H9a3 3 0 00-3 3m12 0c0 1.657-1.343 3-3 3H9c-1.657 0-3-1.343-3-3" />
                    </svg>
                    <h1 class="text-2xl font-bold text-white">
                        Budget<span class="text-lime-400">Boss</span>
                    </h1>
                </Link>
                <h2 class="text-2xl font-bold text-white">Create Your Account</h2>
                <p class="text-gray-400">Join us and take control of your finances.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                 <div class="relative">
                    <User class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="form.name"
                        id="name"
                        type="text"
                        placeholder="Name"
                        required
                        autofocus
                        autocomplete="name"
                        class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all"
                    />
                     <div v-if="form.errors.name" class="text-red-400 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="form.email"
                        id="email"
                        type="email"
                        placeholder="Email"
                        required
                        autocomplete="username"
                        class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all"
                    />
                     <div v-if="form.errors.email" class="text-red-400 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="relative">
                    <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="form.password"
                        id="password"
                        type="password"
                        placeholder="Password"
                        required
                        autocomplete="new-password"
                        class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all"
                    />
                    <div v-if="form.errors.password" class="text-red-400 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                 <div class="relative">
                    <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        v-model="form.password_confirmation"
                        id="password_confirmation"
                        type="password"
                        placeholder="Confirm Password"
                        required
                        autocomplete="new-password"
                        class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-lime-500 focus:border-lime-500 transition-all"
                    />
                    <div v-if="form.errors.password_confirmation" class="text-red-400 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
                </div>


                <button
                    type="submit"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 rounded-md bg-lime-500 px-5 py-3 text-sm font-semibold text-gray-900 shadow-lg hover:bg-lime-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-500 transition-transform hover:scale-105"
                >
                    <LogIn class="h-5 w-5"/>
                    <span>Create Account</span>
                </button>
            </form>

             <p class="text-center text-sm text-gray-400">
                Already have an account?
                <Link :href="route('login')" class="font-medium text-lime-400 hover:text-lime-300">
                    Log In
                </Link>
            </p>
        </div>
    </div>
</template>

<style>
/* Re-using styles from Welcome.vue for consistency */
@import url('https://rsms.me/inter/inter.css');
html {
    font-family: 'Inter', sans-serif;
    scroll-behavior: smooth;
}
.bg-grid-gray-800\/\[0\.2\] {
    background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 40px 40px;
}
@keyframes pulse-slow {
    0%, 100% { transform: scale(1); opacity: 0.1; }
    50% { transform: scale(1.2); opacity: 0.15; }
}
.animate-pulse-slow {
    animation: pulse-slow 8s infinite ease-in-out;
}
.animate-pulse-slow-delay {
    animation: pulse-slow 8s infinite ease-in-out 4s;
}
</style>
