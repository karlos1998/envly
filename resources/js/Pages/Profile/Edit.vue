<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { useTranslations } from '@/composables/useTranslations';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('profile.title')" />

    <AuthenticatedLayout>
        <div class="profile-page">
            <section class="profile-hero ops-card">
                <div>
                    <div class="ops-kicker mb-3">ACCOUNT / SECURITY / LOCALE</div>
                    <h1 class="profile-title">{{ t('profile.title') }}</h1>
                    <p class="ops-muted profile-subtitle">{{ t('profile.subtitle') }}</p>
                </div>

                <div class="profile-signal" aria-hidden="true">
                    <span>AUTH</span>
                    <strong>active</strong>
                    <span>LANG</span>
                    <strong>mutable</strong>
                    <span>THEME</span>
                    <strong>system</strong>
                </div>
            </section>

            <v-row class="mt-6" align="stretch">
                <v-col cols="12" lg="6">
                    <v-card class="ops-card profile-card pa-6" rounded="xl">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </v-card>
                </v-col>

                <v-col cols="12" lg="6">
                    <v-card class="ops-card profile-card pa-6" rounded="xl">
                        <UpdatePasswordForm />
                    </v-card>
                </v-col>

                <v-col cols="12">
                    <v-card class="ops-card profile-card profile-card--danger pa-6" rounded="xl">
                        <DeleteUserForm />
                    </v-card>
                </v-col>
            </v-row>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-page {
    padding-block: clamp(18px, 3vw, 42px);
}

.profile-hero {
    position: relative;
    overflow: hidden;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(230px, 320px);
    gap: 28px;
    align-items: end;
    padding: clamp(24px, 4vw, 42px);
    border-radius: 28px;
}

.profile-hero::before {
    position: absolute;
    inset: auto -20% -60% 38%;
    width: 520px;
    height: 520px;
    content: '';
    background: radial-gradient(circle, color-mix(in srgb, var(--envly-primary) 18%, transparent), transparent 64%);
    pointer-events: none;
}

.profile-title {
    max-width: 820px;
    color: var(--envly-text);
    font-size: clamp(42px, 8vw, 86px);
    font-weight: 950;
    letter-spacing: -0.085em;
    line-height: 0.92;
}

.profile-subtitle {
    max-width: 640px;
    margin-top: 18px;
    font-size: 16px;
    line-height: 1.8;
}

.profile-signal {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px 18px;
    padding: 18px;
    border: 1px solid color-mix(in srgb, var(--envly-primary) 18%, transparent);
    border-radius: 18px;
    background: color-mix(in srgb, var(--envly-background) 78%, transparent);
    font-size: 12px;
}

.profile-signal span {
    color: var(--envly-muted);
}

.profile-signal strong {
    color: var(--envly-primary);
    text-align: right;
}

.profile-card {
    min-height: 100%;
}

.profile-card--danger {
    border-color: color-mix(in srgb, rgb(var(--v-theme-error)) 34%, transparent) !important;
}

@media (max-width: 900px) {
    .profile-hero {
        grid-template-columns: 1fr;
    }
}
</style>
