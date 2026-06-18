/** @type {import('tailwindcss').Config} */
import typography from '@tailwindcss/typography';
export default {
    content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],

    theme: {
        extend: {
            fontFamily: {
                heading: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                equator: {
                    dark: '#263592',
                    darker: '#141A45',
                    bright: '#006CCD',
                    light: '#80C7E3',
                    orange: '#FFB74D',
                    bg: '#F5F5F5',
                    text: '#333333',
                },
            },
        },
    },

    plugins: [require('@tailwindcss/typography'), typography],
};
