// tailwind.config.js (buat di root project lo)
/** @type {import('tailwindcss').Config} */
module.exports = {
  // Ini penting banget buat dark mode
  darkMode: 'class',

  // Pastiin ini nge-scan semua file yang pake Tailwind
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue', // Ini buat komponen Vue lo
    './resources/js/**/*.{js,ts}', // Kalo ada file JS/TS lain yang pake Tailwind
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}