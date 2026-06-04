import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import tsconfigPaths from 'vite-tsconfig-paths';

export default defineConfig({
  base: "./",
  plugins: [
    react(),
    tailwindcss(),
    tsconfigPaths(),
  ],
  server: {
    port: 5189,
    strictPort: true,
  },
  preview: {
    port: 5189,
    strictPort: true,
  },
  resolve: {
    alias: {
      '@': '/src',
    },
  },
});
