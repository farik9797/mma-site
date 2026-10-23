/** Сборка CSS темы MMA: npx tailwindcss@3.4.16 -c tailwind.config.js -i src/input.css -o mma/assets/css/main.css --minify */
module.exports = {
  content: ['./mma/**/*.php', './mma/assets/js/main.js'],
  theme: {
    extend: {
      colors: {
        navy: { DEFAULT: '#102B46', 950: '#0C2238' },
        accent: { DEFAULT: '#1769C2', 600: '#125AA8', 300: '#5FA0EA' },
        mist: '#F3F6F9',
        ink: '#263445',
        muted: '#5B6B7D',
        line: '#DFE6EE',
      },
      fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
      maxWidth: { container: '1240px' },
    },
  },
};
