// ssr.ts
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from 'vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, h, type DefineComponent } from 'vue';
import { ZiggyVue } from 'ziggy-js';

// Vuetify (SSR)
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

const vuetify = createVuetify({
  ssr: true, // recomendado para SSR do Vuetify 3
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
  (page) =>
    createInertiaApp({
      page,
      render: renderToString,
      title: (title) => (title ? `${title} - ${appName}` : appName),
      resolve: resolvePage,
      setup: ({ App, props, plugin }) =>
        createSSRApp({ render: () => h(App, props) })
          .use(plugin)
          .use(vuetify) // <- importante no SSR
          .use(ZiggyVue, {
            ...page.props.ziggy,
            location: new URL(page.props.ziggy.location),
          }),
    }),
  { cluster: true },
);

function resolvePage(name: string) {
  const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
  return resolvePageComponent<DefineComponent>(`./pages/${name}.vue`, pages);
}
