<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import { useTranslations } from '@/composables/useTranslations';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const { t } = useTranslations();
const confirmingUserDeletion = ref(false);
const passwordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section>
        <div class="ops-kicker mb-2">{{ t('profile.delete') }}</div>
        <h2 class="text-h5 font-weight-black mb-2">{{ t('profile.delete_account') }}</h2>
        <p class="ops-muted mb-6">{{ t('profile.delete_hint') }}</p>

        <v-btn color="error" variant="tonal" rounded="lg" prepend-icon="mdi-alert-octagon-outline" @click="confirmUserDeletion">
            {{ t('profile.delete_account') }}
        </v-btn>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="pa-6">
                <div class="ops-kicker mb-2">{{ t('profile.delete') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ t('profile.delete_confirm_title') }}</h2>
                <p class="ops-muted mb-6">{{ t('profile.delete_confirm_body') }}</p>

                <v-text-field
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    :label="t('auth.password')"
                    :error-messages="form.errors.password"
                    autocomplete="current-password"
                    type="password"
                    variant="outlined"
                    @keyup.enter="deleteUser"
                />

                <div class="d-flex justify-end ga-3 mt-2">
                    <v-btn variant="text" rounded="lg" @click="closeModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                    <v-btn color="error" rounded="lg" :loading="form.processing" @click="deleteUser">
                        {{ t('profile.delete_account') }}
                    </v-btn>
                </div>
            </div>
        </Modal>
    </section>
</template>
