<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const { t } = useTranslations();
</script>

<template>
    <GuestLayout>
        <Head :title="t('app.name')" />

        <div class="ops-kicker mb-2">CONTROL PLANE</div>
        <v-card-title class="text-h4 font-weight-black pa-0 mb-3">
            {{ t('app.name') }}
        </v-card-title>
        <v-card-text class="pa-0 ops-muted mb-6">
            {{ t('app.tagline') }}
        </v-card-text>
        <v-btn :href="route('register')" color="primary" size="large" block>
            {{ t('nav.register') }}
        </v-btn>
        <Link :href="route('login')" class="d-block text-center mt-4 text-primary text-decoration-none">
            {{ t('nav.login') }}
        </Link>
    </GuestLayout>
</template>
