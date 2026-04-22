<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Webpass from '@laragear/webpass/dist/webpass.mjs';
import { ref } from 'vue';

defineProps<{ canResetPassword?: boolean; status?: string }>();
const { t } = useTranslations();

const form = useForm({ email: '', password: '', remember: false });
const isPasskeyLoading = ref(false);
const passkeyError = ref<string | null>(null);

const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });

const loginWithPasskey = async (): Promise<void> => {
    passkeyError.value = null;

    if (Webpass.isUnsupported()) {
        passkeyError.value = t('profile.passkeys_unsupported');

        return;
    }

    isPasskeyLoading.value = true;

    const { success, error } = await Webpass.assert(route('webauthn.login.options'), route('webauthn.login'));

    isPasskeyLoading.value = false;

    if (!success) {
        passkeyError.value = error instanceof Error ? error.message : t('auth.passkey_login_failed');

        return;
    }

    window.location.assign(route('dashboard'));
};
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
        <v-alert v-if="passkeyError" class="mb-4" color="error" variant="tonal">{{ passkeyError }}</v-alert>

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

            <v-btn
                type="button"
                color="secondary"
                prepend-icon="mdi-fingerprint"
                :loading="isPasskeyLoading"
                variant="tonal"
                block
                @click="loginWithPasskey"
            >
                {{ t('auth.login_with_passkey') }}
            </v-btn>

            <v-btn :href="route('auth.github.redirect')" color="secondary" prepend-icon="mdi-github" variant="outlined" block>
                {{ t('auth.login_with_github') }}
            </v-btn>

            <Link v-if="canResetPassword" :href="route('password.request')" class="text-center text-primary text-decoration-none">
                Forgot password?
            </Link>
        </form>
    </GuestLayout>
</template>
