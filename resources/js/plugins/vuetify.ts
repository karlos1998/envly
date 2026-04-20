import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

export const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
    },
    defaults: {
        VTextField: {
            color: 'primary',
            rounded: 'lg',
        },
        VSelect: {
            color: 'primary',
            rounded: 'lg',
        },
        VBtn: {
            rounded: 'lg',
            textTransform: 'none',
        },
        VCard: {
            elevation: 0,
        },
    },
    theme: {
        defaultTheme: 'envlyLight',
        themes: {
            envlyLight: {
                dark: false,
                colors: {
                    background: '#f6f3ea',
                    surface: '#fffaf0',
                    primary: '#225c4d',
                    secondary: '#c86b3c',
                    accent: '#172a3a',
                    error: '#b42318',
                    info: '#2563eb',
                    success: '#287a4b',
                    warning: '#c77700',
                },
            },
        },
    },
});
