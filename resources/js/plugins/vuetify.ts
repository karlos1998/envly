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
        VTextarea: {
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
        defaultTheme: 'envlyOps',
        themes: {
            envlyOps: {
                dark: true,
                colors: {
                    background: '#071017',
                    surface: '#0d1821',
                    primary: '#59f3b7',
                    secondary: '#7dd3fc',
                    accent: '#f6c177',
                    error: '#ff6b6b',
                    info: '#7dd3fc',
                    success: '#59f3b7',
                    warning: '#f6c177',
                },
            },
        },
    },
});
