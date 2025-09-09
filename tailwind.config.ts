import type { Config } from 'tailwindcss'

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
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

    ],

} satisfies Config // 'satisfies Config' aplica a tipagem do Tailwind
