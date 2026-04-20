<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import EnvRowsEditor from '@/Components/Env/EnvRowsEditor.vue';
import EnvTextEditor from '@/Components/Env/EnvTextEditor.vue';
import { useTranslations } from '@/composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Project, ProjectEnvironment } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{ project: Project }>();
const { t } = useTranslations();

const selectedEnvironmentId = ref<number>(props.project.environments[0]?.id ?? 0);
const editorMode = ref<'text' | 'rows'>('text');

const activeEnvironment = computed<ProjectEnvironment | undefined>(() =>
    props.project.environments.find((environment) => environment.id === selectedEnvironmentId.value),
);

const contentForm = useForm({ content: activeEnvironment.value?.content ?? '' });
const environmentForm = useForm({ name: '', content: '' });
const tokenForm = useForm({ current_password: '' });
const confirmingTokenRegeneration = ref(false);
const tokenPasswordInput = ref<{ focus: () => void } | null>(null);

const apiUrl = computed(() => {
    if (!activeEnvironment.value) {
        return '';
    }

    return `/api/env/${props.project.identifier}/${activeEnvironment.value.access_token}`;
});

watch(activeEnvironment, (environment) => {
    contentForm.content = environment?.content ?? '';
});

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const saveContent = () => {
    if (!activeEnvironment.value) {
        return;
    }

    contentForm.put(route('projects.environments.update', [props.project.identifier, activeEnvironment.value.id]), {
        preserveScroll: true,
    });
};

const createEnvironment = () => {
    environmentForm.post(route('projects.environments.store', props.project.identifier), {
        preserveScroll: true,
        onSuccess: () => environmentForm.reset(),
    });
};

const openRegenerateTokenModal = () => {
    confirmingTokenRegeneration.value = true;

    nextTick(() => tokenPasswordInput.value?.focus());
};

const closeRegenerateTokenModal = () => {
    confirmingTokenRegeneration.value = false;
    tokenForm.clearErrors();
    tokenForm.reset();
};

const regenerateToken = () => {
    if (!activeEnvironment.value) {
        return;
    }

    tokenForm.post(route('projects.environments.token', [props.project.identifier, activeEnvironment.value.id]), {
        preserveScroll: true,
        onSuccess: () => closeRegenerateTokenModal(),
        onError: () => tokenPasswordInput.value?.focus(),
        onFinish: () => tokenForm.reset('current_password'),
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="project.name" />

        <v-row>
            <v-col cols="12" lg="3">
                <v-card class="env-card pa-5" rounded="xl">
                    <div class="text-overline text-secondary font-weight-bold">{{ t('projects.identifier') }}</div>
                    <h1 class="text-h4 font-weight-black mb-2">{{ project.name }}</h1>
                    <v-chip color="primary" variant="tonal">{{ project.identifier }}</v-chip>

                    <v-divider class="my-5" />

                    <div class="text-subtitle-1 font-weight-bold mb-3">{{ t('environments.title') }}</div>
                    <v-list bg-color="transparent" density="comfortable">
                        <v-list-item
                            v-for="environment in project.environments"
                            :key="environment.id"
                            :active="selectedEnvironmentId === environment.id"
                            rounded="lg"
                            @click="selectedEnvironmentId = environment.id"
                        >
                            <v-list-item-title>{{ environment.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>

                    <v-divider class="my-5" />

                    <form @submit.prevent="createEnvironment">
                        <v-text-field
                            v-model="environmentForm.name"
                            :label="t('environments.name')"
                            :error-messages="environmentForm.errors.name"
                            variant="outlined"
                            density="comfortable"
                        />
                        <v-btn type="submit" color="primary" rounded="xl" block :loading="environmentForm.processing">
                            {{ t('environments.create') }}
                        </v-btn>
                    </form>
                </v-card>
            </v-col>

            <v-col cols="12" lg="6">
                <v-card v-if="activeEnvironment" class="env-card pa-5" rounded="xl">
                    <div class="d-flex align-center justify-space-between mb-4 ga-4 flex-wrap">
                        <div>
                            <div class="text-overline text-secondary font-weight-bold">{{ activeEnvironment.slug }}</div>
                            <h2 class="text-h4 font-weight-black">{{ activeEnvironment.name }}</h2>
                        </div>
                        <v-btn-toggle v-model="editorMode" class="editor-mode-toggle" color="primary" divided rounded="xl" mandatory>
                            <v-btn value="text">{{ t('environments.text_mode') }}</v-btn>
                            <v-btn value="rows">{{ t('environments.rows_mode') }}</v-btn>
                        </v-btn-toggle>
                    </div>

                    <EnvTextEditor v-if="editorMode === 'text'" v-model="contentForm.content" />
                    <EnvRowsEditor v-else v-model="contentForm.content" />

                    <v-alert v-if="contentForm.errors.content" class="mt-4" type="error" variant="tonal">
                        {{ contentForm.errors.content }}
                    </v-alert>

                    <div class="editor-save-bar mt-5">
                        <div class="editor-save-panel">
                            <v-btn
                                block
                                size="x-large"
                                rounded="xl"
                                variant="flat"
                                prepend-icon="mdi-content-save"
                                class="save-env-btn"
                                :loading="contentForm.processing"
                                @click="saveContent"
                            >
                                {{ t('environments.save') }}
                            </v-btn>
                        </div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" lg="3">
                <v-card v-if="activeEnvironment" class="env-card pa-5 mb-5" rounded="xl">
                    <div class="text-subtitle-1 font-weight-bold mb-2">{{ t('environments.token') }}</div>
                    <v-text-field :model-value="activeEnvironment.access_token" readonly variant="outlined" density="compact" />
                    <div class="text-caption text-medium-emphasis mb-2">{{ t('environments.api_url') }}</div>
                    <v-textarea :model-value="apiUrl" readonly variant="outlined" rows="3" density="compact" />
                    <p class="text-body-2 mb-4">{{ t('projects.api_hint') }}</p>
                    <v-btn color="secondary" variant="flat" rounded="xl" block prepend-icon="mdi-lock-reset" @click="openRegenerateTokenModal">
                        {{ t('environments.regenerate_token') }}
                    </v-btn>
                </v-card>

                <v-card v-if="activeEnvironment" class="env-card pa-5" rounded="xl">
                    <div class="text-subtitle-1 font-weight-bold mb-3">{{ t('environments.history') }}</div>
                    <v-expansion-panels variant="accordion">
                        <v-expansion-panel
                            v-for="version in activeEnvironment.versions"
                            :key="version.id"
                            rounded="lg"
                            class="mb-3 history-panel"
                        >
                            <v-expansion-panel-title>
                                <div class="w-100 pr-4">
                                    <div class="font-weight-bold">{{ version.summary }}</div>
                                    <div class="text-caption text-medium-emphasis mt-1">
                                        +{{ version.added_lines }} {{ t('history.added') }}, -{{ version.removed_lines }} {{ t('history.removed') }}
                                    </div>
                                    <div class="text-caption mt-1">
                                        {{ t('history.changed_by') }}: {{ version.creator?.name ?? '—' }}
                                        <span v-if="version.created_at">· {{ formatDateTime(version.created_at) }}</span>
                                    </div>
                                </div>
                            </v-expansion-panel-title>
                            <v-expansion-panel-text>
                                <div class="text-subtitle-2 font-weight-bold mb-3">{{ t('history.details') }}</div>

                                <template v-if="version.has_content_changes">
                                    <div class="history-detail-grid">
                                        <div>
                                            <div class="text-caption text-medium-emphasis mb-2">{{ t('history.before') }}</div>
                                            <pre class="history-content">{{ version.previous_content || t('history.no_previous_content') }}</pre>
                                        </div>
                                        <div>
                                            <div class="text-caption text-medium-emphasis mb-2">{{ t('history.after') }}</div>
                                            <pre class="history-content">{{ version.content }}</pre>
                                        </div>
                                    </div>
                                </template>

                                <v-alert v-else type="info" variant="tonal" density="comfortable">
                                    {{ t('history.no_content_changes') }} {{ t('history.token_regenerated_note') }}
                                </v-alert>
                            </v-expansion-panel-text>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </v-card>
            </v-col>
        </v-row>

        <Modal :show="confirmingTokenRegeneration" max-width="md" @close="closeRegenerateTokenModal">
            <div class="pa-6">
                <div class="ops-kicker mb-2">{{ t('environments.token') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ t('environments.regenerate_token_confirm_title') }}</h2>
                <p class="ops-muted mb-6">{{ t('environments.regenerate_token_confirm_body') }}</p>

                <v-text-field
                    id="current_password"
                    ref="tokenPasswordInput"
                    v-model="tokenForm.current_password"
                    :label="t('profile.current_password')"
                    :error-messages="tokenForm.errors.current_password"
                    autocomplete="current-password"
                    type="password"
                    variant="outlined"
                    @keyup.enter="regenerateToken"
                />

                <div class="d-flex justify-end ga-3 mt-2">
                    <v-btn variant="text" rounded="lg" @click="closeRegenerateTokenModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                    <v-btn color="secondary" rounded="lg" :loading="tokenForm.processing" @click="regenerateToken">
                        {{ t('environments.regenerate_token_confirm_action') }}
                    </v-btn>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.editor-mode-toggle {
    border: 1px solid rgba(15, 143, 114, 0.25);
    background: rgba(15, 143, 114, 0.08);
}

.editor-mode-toggle :deep(button) {
    color: inherit;
    min-width: 132px;
}

.editor-mode-toggle :deep(.v-btn--active) {
    background: #0f8f72;
    color: #ffffff;
}

.editor-save-bar {
    padding-top: 1rem;
    border-top: 1px solid rgba(15, 143, 114, 0.12);
}

.editor-save-panel {
    padding: 1rem;
    border-radius: 1.25rem;
    background: linear-gradient(135deg, rgba(89, 243, 183, 0.14), rgba(15, 143, 114, 0.06));
    border: 1px solid rgba(89, 243, 183, 0.2);
}

.save-env-btn {
    min-height: 56px;
    font-weight: 800;
    letter-spacing: 0.02em;
    box-shadow: 0 18px 36px rgba(15, 143, 114, 0.22);
    background: linear-gradient(135deg, #59f3b7, #34d399) !important;
    color: #04110d !important;
    opacity: 1 !important;
}

.history-panel {
    border: 1px solid rgba(15, 143, 114, 0.16);
}

.history-detail-grid {
    display: grid;
    gap: 1rem;
}

.history-content {
    margin: 0;
    padding: 1rem;
    border-radius: 1rem;
    background: rgba(15, 143, 114, 0.06);
    border: 1px solid rgba(15, 143, 114, 0.16);
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.85rem;
    line-height: 1.5;
}

@media (min-width: 960px) {
    .history-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
