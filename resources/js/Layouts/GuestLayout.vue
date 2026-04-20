<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const { t } = useTranslations();
const canLogin = computed(() => !page.props.auth.user);
</script>

<template>
    <v-app>
        <v-main class="guest-shell">
            <v-container class="py-10" max-width="1120">
                <div class="d-flex align-center justify-space-between mb-10">
                    <Link :href="route('projects.index')" class="brand-link">
                        <span class="brand-mark">E</span>
                        <span>{{ t('app.name') }}</span>
                    </Link>

                    <div v-if="canLogin" class="d-flex ga-2">
                        <v-btn :href="route('login')" variant="text" color="primary">
                            {{ t('nav.login') }}
                        </v-btn>
                        <v-btn :href="route('register')" color="primary" rounded="xl">
                            {{ t('nav.register') }}
                        </v-btn>
                    </div>
                </div>

                <v-row align="center" justify="center">
                    <v-col cols="12" md="5">
                        <div class="hero-kicker">ENVLY</div>
                        <h1 class="hero-title">{{ t('app.tagline') }}</h1>
                    </v-col>
                    <v-col cols="12" md="7" lg="5">
                        <v-card class="env-card pa-8" rounded="xl">
                            <slot />
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.guest-shell {
    min-height: 100vh;
}

.brand-link {
    color: #172a3a;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    font-weight: 800;
    letter-spacing: -0.04em;
    text-decoration: none;
    font-size: 24px;
}

.brand-mark {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 15px;
    background: #225c4d;
    color: #fffaf0;
}

.hero-kicker {
    color: #c86b3c;
    font-weight: 900;
    letter-spacing: 0.32em;
    margin-bottom: 18px;
}

.hero-title {
    font-size: clamp(42px, 7vw, 82px);
    line-height: 0.92;
    letter-spacing: -0.07em;
    max-width: 680px;
}
</style>
