<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const { t } = useTranslations();
const user = computed(() => page.props.auth.user);

const logout = () => router.post(route('logout'));
</script>

<template>
    <v-app>
        <v-app-bar flat color="transparent" class="px-4">
            <Link :href="route('projects.index')" class="brand-link">
                <span class="brand-mark">E</span>
                <span>{{ t('app.name') }}</span>
            </Link>

            <v-spacer />

            <v-btn :href="route('projects.index')" variant="text" color="primary">
                {{ t('nav.projects') }}
            </v-btn>
            <v-btn :href="route('profile.edit')" variant="text" color="primary">
                {{ user?.name }}
            </v-btn>
            <v-btn icon="mdi-logout" variant="text" color="primary" @click="logout" />
        </v-app-bar>

        <v-main>
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
.brand-link {
    color: #172a3a;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 900;
    text-decoration: none;
    font-size: 22px;
    letter-spacing: -0.05em;
}

.brand-mark {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 14px;
    background: #225c4d;
    color: #fffaf0;
}
</style>
