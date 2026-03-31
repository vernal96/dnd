import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./index.html', './src/**/*.{vue,ts}'],
  theme: {
    extend: {
      colors: {
        parchment: '#f4e7d3',
        ember: '#f0b35e',
        gold: '#f8d89b',
        dusk: '#17111f',
        arcane: '#2a1837',
      },
      boxShadow: {
        panel: '0 24px 80px rgba(21, 12, 28, 0.32)',
        glow: '0 0 0 1px rgba(248, 216, 155, 0.15), 0 0 35px rgba(219, 141, 54, 0.2)',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      fontFamily: {
        display: ['Vollkorn Display', 'Vollkorn', 'Georgia', 'serif'],
        body: ['Vollkorn', 'Georgia', 'serif'],
      },
      backgroundImage: {
        'rune-grid':
          'linear-gradient(rgba(248,216,155,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(248,216,155,0.06) 1px, transparent 1px)',
      },
    },
  },
  plugins: [],
};

export default config;
