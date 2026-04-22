<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});
const { t } = useTranslations();

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.verify_email_title')" />

        <div class="verify-email-header mb-8">
            <div class="ops-kicker mb-2">AUTH / EMAIL</div>
            <h2 class="verify-email-title mb-3">{{ t('auth.verify_email_title') }}</h2>
            <p class="verify-email-copy mb-0">
                {{ t('auth.verify_email_intro') }}
            </p>
        </div>

        <div
            class="mb-5"
            v-if="verificationLinkSent"
        >
            <v-alert color="success" density="comfortable" variant="tonal">
                {{ t('auth.verify_email_sent') }}
            </v-alert>
        </div>

        <form @submit.prevent="submit">
            <div class="verify-email-actions d-flex flex-column ga-3">
                <v-btn
                    type="submit"
                    color="primary"
                    size="large"
                    rounded="xl"
                    :loading="form.processing"
                    :disabled="form.processing"
                    block
                >
                    {{ t('auth.resend_verification_email') }}
                </v-btn>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="verify-email-logout text-decoration-none"
                >
                    {{ t('nav.logout') }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.verify-email-header {
    max-width: 460px;
}

.verify-email-title {
    color: var(--envly-text);
    font-size: clamp(26px, 4vw, 34px);
    font-weight: 900;
    letter-spacing: -0.035em;
    line-height: 1.05;
}

.verify-email-copy {
    color: color-mix(in srgb, var(--envly-text) 84%, transparent);
    font-size: 17px;
    line-height: 1.7;
    letter-spacing: -0.015em;
}

.verify-email-actions {
    max-width: 420px;
}

.verify-email-logout {
    align-self: center;
    color: color-mix(in srgb, var(--envly-text) 78%, transparent);
    font-size: 15px;
    font-weight: 700;
    transition: color 0.2s ease, opacity 0.2s ease;
}

.verify-email-logout:hover {
    color: var(--envly-primary);
    opacity: 1;
}

@media (max-width: 600px) {
    .verify-email-copy {
        font-size: 16px;
        line-height: 1.6;
    }
}
</style>
