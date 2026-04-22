<script setup lang="ts">
import AppFooter from '@/Components/AppFooter.vue';
import AppLogo from '@/Components/AppLogo.vue';
import ThemeModeSwitch from '@/Components/ThemeModeSwitch.vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage<PageProps>();
const { t } = useTranslations();
const user = computed(() => page.props.auth.user);
const toast = ref({
    show: false,
    message: '',
    color: 'success',
});

const logout = () => router.post(route('logout'));

watch(
    () => [page.props.flash.success, page.props.flash.error],
    ([success, error]) => {
        const message = success ?? error;

        if (!message) {
            return;
        }

        toast.value = {
            show: true,
            message,
            color: success ? 'success' : 'error',
        };
    },
    { immediate: true },
);
</script>

<template>
    <v-app>
        <v-app-bar flat color="transparent" class="ops-app-bar px-4">
            <Link :href="route('projects.index')" class="brand-link">
                <AppLogo />
            </Link>

            <v-spacer />

            <v-btn :href="route('projects.index')" variant="text" color="primary">
                {{ t('nav.projects') }}
            </v-btn>
            <v-btn :href="route('profile.edit')" variant="text" color="secondary">
                {{ user?.name }}
            </v-btn>
            <ThemeModeSwitch />
            <v-btn icon="mdi-logout" variant="text" color="primary" @click="logout" />
        </v-app-bar>

        <v-main class="ops-main">
            <v-container class="py-8" max-width="1320">
                <slot />
                <AppFooter />
            </v-container>
        </v-main>

        <v-snackbar
            v-model="toast.show"
            :color="toast.color"
            location="top right"
            rounded="lg"
            timeout="3200"
            variant="flat"
            class="ops-toast"
        >
            <div class="d-flex align-center ga-3">
                <v-icon :icon="toast.color === 'success' ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline'" />
                <span>{{ toast.message }}</span>
            </div>

            <template #actions>
                <v-btn icon="mdi-close" variant="text" @click="toast.show = false" />
            </template>
        </v-snackbar>
    </v-app>
</template>

<style scoped>
.ops-app-bar {
    border-bottom: 1px solid rgba(89, 243, 183, 0.12);
    background: color-mix(in srgb, var(--envly-background) 88%, transparent) !important;
    backdrop-filter: blur(18px);
}

.ops-main {
    min-height: 100vh;
}

.brand-link {
    text-decoration: none;
}

.ops-toast :deep(.v-snackbar__wrapper) {
    border: 1px solid color-mix(in srgb, var(--envly-primary) 22%, transparent);
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
}
</style>
