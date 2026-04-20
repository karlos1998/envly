<script setup lang="ts">
import AppLogo from '@/Components/AppLogo.vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<PageProps>();
const { t } = useTranslations();
const user = computed(() => page.props.auth.user);

const logout = () => router.post(route('logout'));
</script>

<template>
    <v-app>
        <v-app-bar flat color="rgba(7, 16, 23, 0.88)" class="ops-app-bar px-4">
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
            <v-btn icon="mdi-logout" variant="text" color="primary" @click="logout" />
        </v-app-bar>

        <v-main class="ops-main">
            <v-container class="py-8" max-width="1320">
                <v-alert
                    v-if="page.props.flash.success"
                    class="mb-5"
                    color="success"
                    variant="tonal"
                    rounded="lg"
                >
                    {{ page.props.flash.success }}
                </v-alert>

                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.ops-app-bar {
    border-bottom: 1px solid rgba(89, 243, 183, 0.12);
    backdrop-filter: blur(18px);
}

.ops-main {
    min-height: 100vh;
}

.brand-link {
    text-decoration: none;
}
</style>
