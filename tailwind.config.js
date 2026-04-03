import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                outfit:  ['Outfit', ...defaultTheme.fontFamily.sans],
                heading: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'fade-in':  { from: { opacity: '0' }, to: { opacity: '1' } },
                'slide-up': { from: { opacity: '0', transform: 'translateY(8px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
            },
            animation: {
                'fade-in':  'fade-in 200ms ease',
                'slide-up': 'slide-up 250ms ease',
            },
        },
    },

    plugins: [forms],
};
