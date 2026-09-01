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
            colors: {
                primary: '#E11D48',
                secondary: '#2563EB',
                tertiary: '#FACC15',
                surface: {
                    base: '#FFFFFF',
                    glass: 'rgba(255, 255, 255, 0.65)',
                },
                success: '#16A34A',
                warning: '#D97706',
                error: '#DC2626',
                info: '#2563EB',
            },
            fontFamily: {
                headline: ['Poppins', ...defaultTheme.fontFamily.sans],
                body: ['DM Sans', ...defaultTheme.fontFamily.sans],
                mono: ['Fira Code', ...defaultTheme.fontFamily.mono],
            },
            spacing: {
                '1': '4px',
                '2': '8px',
                '3': '16px',
                '4': '24px',
                '5': '32px',
                '6': '48px',
                '8': '64px',
                '10': '80px',
            },
            borderRadius: {
                'sm': '4px',
                'md': '8px',
                'lg': '16px',
                'xl': '24px',
                'pill': '9999px',
            },
            boxShadow: {
                'glass': '0 8px 32px rgba(0, 0, 0, 0.08)',
                'md': '0 4px 16px rgba(0, 0, 0, 0.10)',
                'lg': '0 12px 40px rgba(0, 0, 0, 0.15)',
                'color': '0 8px 24px rgba(225, 29, 72, 0.25)',
                'focus': '0 0 0 3px rgba(37, 99, 235, 0.35)',
            },
        },
    },

    plugins: [forms],
};
