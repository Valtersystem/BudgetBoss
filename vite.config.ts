import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vuetify, { transformAssetUrls } from 'vite-plugin-vuetify';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      ssr: 'resources/js/ssr.ts',
      refresh: true,
    }),
    tailwindcss(),
    vue({
      template: {
        // usa o transformAssetUrls do plugin do Vuetify
        transformAssetUrls,
      },
    }),
    // habilita auto-import de componentes e directives do Vuetify
    vuetify({ autoImport: true }),
  ],

  // necessário para SSR com Vuetify
  ssr: {
    noExternal: ['vuetify'],
  },
});
