import type { Config } from 'tailwindcss'

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts', // Adicionei .ts para scripts TypeScript
        './resources/**/*.vue',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
               ' header-table' : 'hsl(var(--text-header-table))',
            },
        },
    },

    plugins: [
        // require('flowbite/plugin'), // Descomente se for necessário
    ],

} satisfies Config // 'satisfies Config' aplica a tipagem do Tailwind
