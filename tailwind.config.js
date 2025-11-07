// tailwind.config.js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        ccsblue: '#0055ff',
        ccsgray: '#f1f1f1',
        primary: '#1E40AF',
        secondary: '#FBBF24',
        mediumgray: '#9ca3af',    // ✅ Only once!
        lightgray: '#e5e7eb',
        darkgray: '#374151',
        maroon: '#800000', 
      },
    },
  },
  plugins: [],
}
