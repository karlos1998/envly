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
        <h2 class="text-h5 font-weight-black mb-2">{{ t('nav.profile') }}</h2>

        <form class="d-flex flex-column ga-3 mt-6" @submit.prevent="form.patch(route('profile.update'))">
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
                <p class="text-body-2">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button" class="text-primary">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <v-alert v-if="props.status === 'verification-link-sent'" class="mt-3" color="success" variant="tonal">
                    A new verification link has been sent to your email address.
                </v-alert>
            </div>

            <div class="d-flex align-center ga-4">
                <v-btn type="submit" color="primary" rounded="xl" :loading="form.processing">
                    Save
                </v-btn>
                <span v-if="form.recentlySuccessful" class="text-medium-emphasis">Saved.</span>
            </div>
        </form>
    </section>
</template>
