import { useTheme } from 'vuetify';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

export type ThemePreference = 'system' | 'dark' | 'light';
export type ResolvedTheme = 'dark' | 'light';

const storageKey = 'envly.theme.preference';
const darkThemeName = 'envlyOpsDark';
const lightThemeName = 'envlyOpsLight';
const validPreferences: ThemePreference[] = ['system', 'dark', 'light'];
const preference = ref<ThemePreference>(readStoredPreference());
const resolvedTheme = ref<ResolvedTheme>(resolveTheme(preference.value));

function hasWindow(): boolean {
    return typeof window !== 'undefined';
}

function getSystemTheme(): ResolvedTheme {
    if (!hasWindow()) {
        return 'dark';
    }

    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

function readStoredPreference(): ThemePreference {
    if (!hasWindow()) {
        return 'system';
    }

    const storedPreference = window.localStorage.getItem(storageKey);

    return validPreferences.includes(storedPreference as ThemePreference)
        ? (storedPreference as ThemePreference)
        : 'system';
}

function resolveTheme(nextPreference: ThemePreference): ResolvedTheme {
    return nextPreference === 'system' ? getSystemTheme() : nextPreference;
}

export function getInitialVuetifyTheme(): string {
    return resolveTheme(readStoredPreference()) === 'dark' ? darkThemeName : lightThemeName;
}

export function syncInitialDocumentTheme(): void {
    syncDocumentTheme(resolveTheme(readStoredPreference()));
}

function syncDocumentTheme(nextResolvedTheme: ResolvedTheme): void {
    if (!hasWindow()) {
        return;
    }

    document.documentElement.dataset.colorScheme = nextResolvedTheme;
    document.documentElement.style.colorScheme = nextResolvedTheme;
}

export function useThemePreference() {
    const vuetifyTheme = useTheme();
    const activeThemeName = computed(() => (resolvedTheme.value === 'dark' ? darkThemeName : lightThemeName));

    const applyTheme = (nextPreference: ThemePreference = preference.value): void => {
        const nextResolvedTheme = resolveTheme(nextPreference);

        preference.value = nextPreference;
        resolvedTheme.value = nextResolvedTheme;
        vuetifyTheme.global.name.value = nextResolvedTheme === 'dark' ? darkThemeName : lightThemeName;
        syncDocumentTheme(nextResolvedTheme);

        if (hasWindow()) {
            window.localStorage.setItem(storageKey, nextPreference);
        }
    };

    const setThemePreference = (nextPreference: ThemePreference): void => {
        applyTheme(nextPreference);
    };

    const handleSystemThemeChange = (): void => {
        if (preference.value === 'system') {
            applyTheme('system');
        }
    };

    let mediaQuery: MediaQueryList | null = null;

    onMounted(() => {
        applyTheme(preference.value);

        mediaQuery = window.matchMedia('(prefers-color-scheme: light)');
        mediaQuery.addEventListener('change', handleSystemThemeChange);
    });

    onUnmounted(() => {
        mediaQuery?.removeEventListener('change', handleSystemThemeChange);
    });

    watch(preference, (nextPreference) => applyTheme(nextPreference));

    return {
        activeThemeName,
        preference,
        resolvedTheme,
        setThemePreference,
    };
}
