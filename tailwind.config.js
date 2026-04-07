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
                'fade-in':       { from: { opacity: '0' }, to: { opacity: '1' } },
                'slide-up':      { from: { opacity: '0', transform: 'translateY(8px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                'slide-in-top':  { from: { opacity: '0', transform: 'translateY(-12px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                'slide-out-top': { from: { opacity: '1', transform: 'translateY(0)' }, to: { opacity: '0', transform: 'translateY(-12px)' } },
                'bounce-slow':   { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-15px)' } },
                'shimmer':       { '0%': { backgroundPosition: '-200% 0' }, '100%': { backgroundPosition: '200% 0' } },
            },
            animation: {
                'fade-in':      'fade-in 200ms ease',
                'slide-up':     'slide-up 250ms ease',
                'slide-in-top': 'slide-in-top 350ms cubic-bezier(0.16,1,0.3,1)',
                'bounce-slow':  'bounce-slow 4s infinite ease-in-out',
                'shimmer':      'shimmer 2s linear infinite',
            },
        },
    },

    plugins: [forms],
};
