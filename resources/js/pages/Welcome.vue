<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

// SVG icons for the features section
const features = [
  {
    name: 'Smart Budgeting',
    description: 'Create custom budgets that work for you. Set limits and track your progress effortlessly.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />`,
  },
  {
    name: 'Expense Tracking',
    description: 'Log all your transactions quickly and easily. Categorize your expenses to see where your money is going.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h6m3-3.75l-3 1.5m3-1.5l-3-1.5m-6 1.5l3-1.5m-3 1.5l3 1.5m7.5-3.75h3m-3 2.25h3m4.5-3.75h.75a2.25 2.25 0 012.25 2.25v.75c0 .58-.224 1.133-.62 1.544l-3.23 3.23a2.25 2.25 0 00-.62 1.544v.75a2.25 2.25 0 01-2.25 2.25h-.75a2.25 2.25 0 01-2.25-2.25v-.75a2.25 2.25 0 00-.62-1.544l-3.23-3.23a2.25 2.25 0 00-.62-1.544V11.25a2.25 2.25 0 012.25-2.25h.75" />`
  },
  {
    name: 'Financial Reports',
    description: 'Get visual reports and clear insights into your financial health. Make smarter decisions based on concrete data.',
    icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15z" /><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 10.5V15h4.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 015.25 12.75" />`
  }
]
</script>

<template>
    <Head title="Welcome to BudgetBoss" />
    <div class="relative w-full overflow-hidden bg-gray-900 text-gray-100 font-sans">
        <!-- Background gradient effect -->
        <div class="absolute top-0 left-0 w-full h-full bg-grid-gray-800/[0.2] z-0">
             <div class="absolute pointer-events-none inset-0 flex items-center justify-center bg-gray-900 [mask-image:radial-gradient(ellipse_at_center,transparent_20%,black)]"></div>
        </div>
        <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] rounded-full bg-lime-500/10 blur-3xl z-0 animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] rounded-full bg-violet-500/10 blur-3xl z-0 animate-pulse-slow-delay"></div>

        <div class="relative z-10 flex flex-col min-h-screen">
            <!-- Navigation Header -->
            <header class="w-full py-6">
                <nav class="container mx-auto flex items-center justify-between flex-wrap gap-y-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-2">
                        <svg class="h-8 w-8 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m15 3H6m12 0a1.5 1.5 0 01-1.5 1.5H9a1.5 1.5 0 01-1.5-1.5m1.5-12a3 3 0 00-3-3H9a3 3 0 00-3 3m12 0c0 1.657-1.343 3-3 3H9c-1.657 0-3-1.343-3-3" />
                        </svg>
                        <h1 class="text-2xl font-bold text-white">
                            Budget<span class="text-lime-400">Boss</span>
                        </h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-semibold leading-6 hover:text-lime-400 transition-colors">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="text-sm font-semibold leading-6 hover:text-lime-400 transition-colors">
                                Log in <span aria-hidden="true">&rarr;</span>
                            </Link>
                            <Link :href="route('register')" class="rounded-md bg-lime-500 px-3.5 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-lime-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-500 transition-colors">
                                Sign Up
                            </Link>
                        </template>
                    </div>
                </nav>
            </header>

            <!-- Hero Section -->
            <main class="flex-grow flex flex-col items-center justify-center text-center container mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="max-w-3xl">
                    <h2 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl">
                        Take Control of Your Finances with
                        <span class="text-lime-400">Confidence</span>
                    </h2>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        The ultimate platform to manage your budget, track expenses, and achieve your financial goals. Transform your relationship with money, today.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-x-6">
                        <Link :href="route('register')" class="w-full sm:w-auto rounded-md bg-lime-500 px-5 py-3 text-sm font-semibold text-gray-900 shadow-lg hover:bg-lime-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-500 transition-transform hover:scale-105">
                            Get Started for Free
                        </Link>
                        <a href="#features" class="text-sm font-semibold leading-6 text-white hover:text-lime-300 transition-colors">
                            Learn More <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </main>

            <!-- Features Section -->
            <section id="features" class="relative z-10 w-full py-24 sm:py-32 bg-gray-900/50 backdrop-blur-sm">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center">
                        <h2 class="text-base font-semibold leading-7 text-lime-400">Everything You Need</h2>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            A complete platform for your financial life
                        </p>
                        <p class="mt-6 text-lg leading-8 text-gray-300">
                            BudgetBoss offers powerful and intuitive tools so you always have full control of your money.
                        </p>
                    </div>
                    <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3 lg:gap-y-16">
                            <div v-for="feature in features" :key="feature.name" class="relative flex flex-col items-center text-center p-6 rounded-2xl border border-white/10 bg-white/5 transition-all hover:bg-white/10 hover:border-lime-400/50">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-lime-500 text-gray-900">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" v-html="feature.icon"></svg>
                                </div>
                                <dt class="mt-4 font-semibold text-white text-lg">{{ feature.name }}</dt>
                                <dd class="mt-2 text-base leading-7 text-gray-300">{{ feature.description }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer class="relative z-10 w-full py-8">
                <div class="container mx-auto text-center text-gray-500 px-4 sm:px-6 lg:px-8">
                    &copy; {{ new Date().getFullYear() }} BudgetBoss by 7-FaithTech. All rights reserved.
                </div>
            </footer>
        </div>
    </div>
</template>

<style>
@import url('https://rsms.me/inter/inter.css');
html {
    font-family: 'Inter', sans-serif;
    scroll-behavior: smooth;
}

/* Background grid pattern */
.bg-grid-gray-800\/\[0\.2\] {
    background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* Gradient pulse animations */
@keyframes pulse-slow {
    0%, 100% {
        transform: scale(1);
        opacity: 0.1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.15;
    }
}
.animate-pulse-slow {
    animation: pulse-slow 8s infinite ease-in-out;
}
.animate-pulse-slow-delay {
    animation: pulse-slow 8s infinite ease-in-out 4s;
}
</style>
