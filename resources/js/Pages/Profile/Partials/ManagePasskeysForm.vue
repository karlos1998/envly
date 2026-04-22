<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import type { PasskeyCredential } from '@/types';
import { router } from '@inertiajs/vue3';
import Webpass from '@laragear/webpass/dist/webpass.mjs';
import { ref } from 'vue';

const props = defineProps<{
    passkeys: PasskeyCredential[];
}>();

const { t } = useTranslations();

const isCreating = ref(false);
const feedbackError = ref<string | null>(null);

const createPasskey = async (): Promise<void> => {
    feedbackError.value = null;

    if (Webpass.isUnsupported()) {
        feedbackError.value = t('profile.passkeys_unsupported');

        return;
    }

    isCreating.value = true;

    const { success, error } = await Webpass.attest(route('webauthn.register.options'), route('webauthn.register'));

    isCreating.value = false;

    if (!success) {
        feedbackError.value = error instanceof Error ? error.message : t('profile.passkeys_creation_failed');

        return;
    }

    router.reload({ only: ['passkeys', 'flash'] });
};

const removePasskey = (credentialId: string): void => {
    router.delete(route('webauthn.credentials.destroy', credentialId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <div class="ops-kicker mb-2">PASSKEYS / WEBAUTHN</div>
        <h2 class="text-h5 font-weight-black mb-2">{{ t('profile.passkeys') }}</h2>
        <p class="ops-muted mb-6">{{ t('profile.passkeys_hint') }}</p>

        <v-alert v-if="feedbackError" color="error" variant="tonal" class="mb-4">
            {{ feedbackError }}
        </v-alert>

        <v-btn class="env-action-btn mb-5" variant="flat" prepend-icon="mdi-fingerprint" :loading="isCreating" block @click="createPasskey">
            {{ t('profile.add_passkey') }}
        </v-btn>

        <v-alert v-if="!props.passkeys.length" color="info" variant="tonal">
            {{ t('profile.passkeys_empty') }}
        </v-alert>

        <div v-else class="d-flex flex-column ga-3">
            <v-card v-for="passkey in props.passkeys" :key="passkey.id" variant="outlined" class="pa-4">
                <div class="d-flex align-center justify-space-between ga-3">
                    <div>
                        <div class="font-weight-bold">{{ passkey.alias || t('profile.passkey_default_name') }}</div>
                        <div class="ops-muted text-caption">{{ passkey.origin }}</div>
                    </div>

                    <v-btn color="error" variant="text" size="small" @click="removePasskey(passkey.id)">
                        {{ t('profile.delete_passkey') }}
                    </v-btn>
                </div>
            </v-card>
        </div>
    </div>
</template>
