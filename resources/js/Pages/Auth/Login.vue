<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ canResetPassword?: boolean; status?: string }>();
const { t } = useTranslations();

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.login')" />

        <div class="mb-8">
            <div class="ops-kicker mb-2">AUTH / SESSION</div>
            <h2 class="text-h4 font-weight-black">{{ t('auth.login') }}</h2>
            <p class="ops-muted mt-2 mb-0">Sign in to manage project environments.</p>
        </div>

        <v-alert v-if="status" class="mb-4" color="success" variant="tonal">{{ status }}</v-alert>

        <form class="d-flex flex-column ga-4" @submit.prevent="submit">
            <v-text-field
                v-model="form.email"
                :label="t('auth.email')"
                :error-messages="form.errors.email"
                type="email"
                variant="outlined"
                prepend-inner-icon="mdi-email-outline"
            />
            <v-text-field
                v-model="form.password"
                :label="t('auth.password')"
                :error-messages="form.errors.password"
                type="password"
                variant="outlined"
                prepend-inner-icon="mdi-lock-outline"
            />
            <v-checkbox v-model="form.remember" :label="t('auth.remember')" color="primary" density="compact" hide-details />
            <v-btn type="submit" color="primary" size="large" :loading="form.processing" block>
                {{ t('auth.login') }}
            </v-btn>
            <Link v-if="canResetPassword" :href="route('password.request')" class="text-center text-primary text-decoration-none">
                Forgot password?
            </Link>
        </form>
    </GuestLayout>
</template>
