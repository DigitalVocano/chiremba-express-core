const colors = require('tailwindcss/colors'); // Keep this line as you're using 'colors.blue'

module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue", // If you plan to use Vue components
  ],
  darkMode: 'class', // This is the key to enable class-based dark mode
  theme: {
    extend: {
      colors: {
        // Your custom primary color updated to Blue shades
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        // Adding secondary color as Tailwind's default blue
        secondary: colors.blue, // This imports all the default blue shades (50-900)
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'), // Optional: for better default form styling
  ],
}