<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const { t } = useTranslations();
const passwordInput = ref<{ focus: () => void } | null>(null);
const currentPasswordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <div class="ops-kicker mb-2">{{ t('profile.password') }}</div>
        <h2 class="text-h5 font-weight-black mb-2">{{ t('profile.password') }}</h2>
        <p class="ops-muted mb-6">{{ t('profile.password_hint') }}</p>

        <form class="d-flex flex-column ga-3" @submit.prevent="updatePassword">
            <v-text-field
                id="current_password"
                ref="currentPasswordInput"
                v-model="form.current_password"
                :label="t('profile.current_password')"
                :error-messages="form.errors.current_password"
                autocomplete="current-password"
                type="password"
                variant="outlined"
            />

            <v-text-field
                id="password"
                ref="passwordInput"
                v-model="form.password"
                :label="t('profile.new_password')"
                :error-messages="form.errors.password"
                autocomplete="new-password"
                type="password"
                variant="outlined"
            />

            <v-text-field
                id="password_confirmation"
                v-model="form.password_confirmation"
                :label="t('auth.confirm_password')"
                :error-messages="form.errors.password_confirmation"
                autocomplete="new-password"
                type="password"
                variant="outlined"
            />

            <div class="d-flex align-center ga-4 mt-1">
                <v-btn type="submit" color="primary" rounded="lg" :loading="form.processing">
                    {{ t('profile.save') }}
                </v-btn>
                <span v-if="form.recentlySuccessful" class="ops-muted text-caption">{{ t('profile.saved') }}</span>
            </div>
        </form>
    </section>
</template>
