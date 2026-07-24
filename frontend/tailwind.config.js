/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f4f6fb',
          100: '#e6eaf5',
          200: '#c3cee6',
          300: '#9fb1d6',
          400: '#5c76b3',
          500: '#1e3a8a', // primary — deep, trust-focused navy for a legal product
          600: '#1b3379',
          700: '#162a63',
          800: '#12224f',
          900: '#0e1a3d',
        },
        gold: {
          500: '#b8860b', // muted gold accent, premium/enterprise feel
        },
      },
      fontFamily: {
        serif: ['"Source Serif 4"', 'Georgia', 'serif'],
        sans: ['"Inter"', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
