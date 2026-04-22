<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { LocaleOption, PageProps } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ suggestedLocale: 'en' | 'pl' }>();
const page = usePage<PageProps>();
const { t } = useTranslations();

const form = useForm({
    name: '',
    email: '',
    locale: props.suggestedLocale,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.register')" />

        <form class="d-flex flex-column ga-3" @submit.prevent="submit">
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
            <v-text-field v-model="form.password" :label="t('auth.password')" :error-messages="form.errors.password" type="password" variant="outlined" />
            <v-text-field v-model="form.password_confirmation" :label="t('auth.confirm_password')" type="password" variant="outlined" />

            <v-btn type="submit" color="primary" size="large" rounded="xl" :loading="form.processing" block>
                {{ t('auth.register') }}
            </v-btn>

            <v-btn :href="route('auth.github.redirect')" color="secondary" prepend-icon="mdi-github" variant="outlined" block>
                {{ t('auth.register_with_github') }}
            </v-btn>

            <Link :href="route('login')" class="text-center text-primary">
                {{ t('auth.already_registered') }}
            </Link>
        </form>
    </GuestLayout>
</template>
