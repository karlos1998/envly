<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import type { SocialAccount } from '@/types';
import { useForm } from '@inertiajs/vue3';

const { t } = useTranslations();

const props = defineProps<{
    githubAccount: SocialAccount | null;
}>();

const connectForm = useForm({});
const disconnectForm = useForm({});

const isGithubConnected = (): boolean => Boolean(props.githubAccount);

const connectGithub = (): void => {
    connectForm.post(route('auth.github.connect'));
};

const disconnectGithub = (): void => {
    disconnectForm.delete(route('auth.github.disconnect'));
};
</script>

<template>
    <div>
        <div class="ops-kicker mb-2">SOCIAL / GITHUB</div>
        <h2 class="text-h5 font-weight-black mb-2">GitHub</h2>
        <p class="ops-muted mb-6">
            {{ isGithubConnected() ? t('profile.github_connected_hint') : t('profile.github_disconnected_hint') }}
        </p>

        <v-alert v-if="isGithubConnected()" color="success" variant="tonal" class="mb-4">
            {{ t('profile.github_connected_as') }} {{ githubAccount?.username ?? githubAccount?.email }}
        </v-alert>

        <v-btn
            v-if="!isGithubConnected()"
            color="secondary"
            prepend-icon="mdi-github"
            :loading="connectForm.processing"
            block
            @click="connectGithub"
        >
            {{ t('profile.connect_github') }}
        </v-btn>

        <v-btn
            v-else
            color="error"
            variant="tonal"
            prepend-icon="mdi-link-off"
            :loading="disconnectForm.processing"
            block
            @click="disconnectGithub"
        >
            {{ t('profile.disconnect_github') }}
        </v-btn>
    </div>
</template>
