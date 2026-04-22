<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { useThemePreference, type ThemePreference } from '@/composables/useThemePreference';

const { t } = useTranslations();
const { preference, resolvedTheme, setThemePreference } = useThemePreference();

const themeOptions: { value: ThemePreference; icon: string; labelKey: string }[] = [
    { value: 'system', icon: 'mdi-monitor', labelKey: 'theme.system' },
    { value: 'dark', icon: 'mdi-weather-night', labelKey: 'theme.dark' },
    { value: 'light', icon: 'mdi-white-balance-sunny', labelKey: 'theme.light' },
];
</script>

<template>
    <v-menu location="bottom end" :close-on-content-click="false">
        <template #activator="{ props }">
            <v-btn
                v-bind="props"
                class="theme-trigger"
                :icon="resolvedTheme === 'dark' ? 'mdi-weather-night' : 'mdi-white-balance-sunny'"
                :aria-label="t('theme.label')"
                variant="text"
                color="primary"
            />
        </template>

        <v-card class="ops-card theme-menu pa-2" rounded="lg">
            <div class="px-3 py-2">
                <div class="ops-kicker">{{ t('theme.label') }}</div>
                <div class="ops-muted text-caption">{{ t('theme.system_hint') }}</div>
            </div>

            <v-list class="theme-list" density="compact" bg-color="transparent">
                <v-list-item
                    v-for="option in themeOptions"
                    :key="option.value"
                    :active="preference === option.value"
                    rounded="lg"
                    @click="setThemePreference(option.value)"
                >
                    <template #prepend>
                        <v-icon :icon="option.icon" />
                    </template>
                    <v-list-item-title>{{ t(option.labelKey) }}</v-list-item-title>
                    <template #append>
                        <v-icon v-if="preference === option.value" icon="mdi-check" color="primary" size="18" />
                    </template>
                </v-list-item>
            </v-list>
        </v-card>
    </v-menu>
</template>

<style scoped>
.theme-trigger {
    border: 1px solid rgba(var(--v-theme-primary), 0.18);
}

.theme-menu {
    min-width: 240px;
}

.theme-list :deep(.v-list-item--active) {
    background: rgba(var(--v-theme-primary), 0.12);
    color: rgb(var(--v-theme-primary));
}
</style>
