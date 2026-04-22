import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { vuetify } from './plugins/vuetify';

const appName = import.meta.env.VITE_APP_NAME || 'Envly';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
            ),
        setup({ App, props, plugin }) {
            const ziggy = page.props.ziggy as Record<string, unknown> & { location: string };
            const ziggyConfig = {
                ...ziggy,
                location: new URL(ziggy.location),
            };

            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue as any, ziggyConfig as any)
                .use(vuetify);
        },
    }),
);
