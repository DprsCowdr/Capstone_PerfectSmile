/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",   // ✅ scan all your PHP view files
    "./app/Views/**/*.html",  // ✅ if you have html files in views
    "./resources/**/*.js",    // ✅ if you use JS with Tailwind classes
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
