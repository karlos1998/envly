import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

import { createVuetify } from 'vuetify';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

export const vuetify = createVuetify({
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: { mdi },
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
