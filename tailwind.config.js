import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                navy: {
                    50: '#f0f4f8',
                    100: '#d9e2ec',
                    200: '#bcccdc',
                    300: '#9fb3c8',
                    400: '#829ab1',
                    500: '#627d98',
                    600: '#486581',
                    700: '#334e68',
                    800: '#243b53',
                    900: '#1B2A4A',
                    950: '#102a43',
                },
                gold: {
                    50: '#fdfbf5',
                    100: '#faf5e6',
                    200: '#f5ebc8',
                    300: '#eeda9e',
                    400: '#e5c46e',
                    500: '#C9A961',
                    600: '#b08d4a',
                    700: '#8f703c',
                    800: '#755a34',
                    900: '#634b2f',
                },
                cream: '#F5F1E8',
                charcoal: '#2C2C2C',
            },
        },
    },

    plugins: [forms],
};