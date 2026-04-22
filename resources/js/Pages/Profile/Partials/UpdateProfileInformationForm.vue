<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import type { LocaleOption, PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const user = page.props.auth.user;
const { t } = useTranslations();

if (!user) {
    throw new Error('Profile page requires an authenticated user.');
}

const form = useForm({
    name: user.name,
    email: user.email,
    locale: user.locale,
});
</script>

<template>
    <section>
        <div class="ops-kicker mb-2">{{ t('profile.account') }}</div>
        <h2 class="text-h5 font-weight-black mb-2">{{ t('profile.account') }}</h2>
        <p class="ops-muted mb-6">{{ t('profile.account_hint') }}</p>

        <form class="d-flex flex-column ga-3" @submit.prevent="form.patch(route('profile.update'))">
            <v-text-field v-model="form.name" :label="t('auth.name')" :error-messages="form.errors.name" variant="outlined" />
            <v-text-field v-model="form.email" :label="t('auth.email')" :error-messages="form.errors.email" type="email" variant="outlined" />
            <v-select
                v-model="form.locale"
                :items="page.props.locales as LocaleOption[]"
                item-title="label"
                item-value="value"
                :label="t('app.language')"
                :error-messages="form.errors.locale"
                variant="outlined"
            />

            <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                <p class="ops-muted text-body-2">
                    {{ t('profile.email_unverified') }}
                    <Link :href="route('verification.send')" method="post" as="button" class="text-primary">
                        {{ t('profile.resend_verification') }}
                    </Link>
                </p>

                <v-alert v-if="props.status === 'verification-link-sent'" class="mt-3" color="success" variant="tonal">
                    {{ t('profile.verification_sent') }}
                </v-alert>
            </div>

            <div class="d-flex align-center ga-4">
                <v-btn type="submit" color="primary" rounded="xl" :loading="form.processing">
                    {{ t('profile.save') }}
                </v-btn>
                <span v-if="form.recentlySuccessful" class="ops-muted text-caption">{{ t('profile.saved') }}</span>
            </div>
        </form>
    </section>
</template>
