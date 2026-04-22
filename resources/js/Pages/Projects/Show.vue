<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import EnvRowsEditor from '@/Components/Env/EnvRowsEditor.vue';
import EnvTextEditor from '@/Components/Env/EnvTextEditor.vue';
import { useTranslations } from '@/composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { EnvironmentVersion, Project, ProjectEnvironment } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps<{
    project: Project;
    github: {
        connected: boolean;
    };
}>();
const { t } = useTranslations();

const selectedEnvironmentId = ref<number>(props.project.environments[0]?.id ?? 0);
const editorMode = ref<'text' | 'rows'>('text');
type EnvironmentCreationMode = 'empty' | 'copy';

const activeEnvironment = computed<ProjectEnvironment | undefined>(() =>
    props.project.environments.find((environment) => environment.id === selectedEnvironmentId.value),
);

const canDeleteEnvironment = computed(() => props.project.environments.length > 1);
const sourceEnvironmentOptions = computed(() =>
    props.project.environments.map((environment) => ({
        title: environment.name,
        value: environment.id,
    })),
);
const selectMenuProps = {
    contained: true,
    contentClass: 'env-select-menu',
    zIndex: 24000,
};

const contentForm = useForm({ content: activeEnvironment.value?.content ?? '' });
const environmentForm = useForm<{
    name: string;
    creation_mode: EnvironmentCreationMode;
    source_environment_id: number | null;
}>({
    name: '',
    creation_mode: 'empty',
    source_environment_id: null,
});
const tokenForm = useForm({ current_password: '' });
const deleteEnvironmentForm = useForm({ name: '' });
const deployForm = useForm({});
const creatingEnvironment = ref(false);
const confirmingTokenRegeneration = ref(false);
const confirmingEnvironmentDeletion = ref(false);
const showingUsageExamples = ref(false);
const selectedHistoryVersion = ref<EnvironmentVersion | null>(null);
const selectedEnvironmentForDeletion = ref<ProjectEnvironment | null>(null);
const tokenPasswordInput = ref<{ focus: () => void } | null>(null);
const environmentNameInput = ref<{ focus: () => void } | null>(null);
const deleteEnvironmentNameInput = ref<{ focus: () => void } | null>(null);
const canRunGithubDeploy = computed(
    () => props.github.connected && !!props.project.github_repository_full_name && !!props.project.github_workflow_id,
);

const apiUrl = computed(() => {
    if (!activeEnvironment.value) {
        return '';
    }

    return route('api.env.show', { projectIdentifier: props.project.identifier });
});

const curlExample = computed(() => {
    if (!activeEnvironment.value) {
        return '';
    }

    return [
        'curl -fsSL \\',
        `  -H "Authorization: Bearer ${activeEnvironment.value.access_token}" \\`,
        `  "${apiUrl.value}"`,
    ].join('\n');
});

const githubActionsExample = computed(() => {
    if (!activeEnvironment.value) {
        return '';
    }

    return [
        'steps:',
        '  - name: Download environment from Envly',
        '    env:',
        `      ENVLY_API_URL: "${apiUrl.value}"`,
        '      ENVLY_TOKEN: ${{ secrets.ENVLY_TOKEN }}',
        '    run: |',
        '      curl -fsSL \\',
        '        -H "Authorization: Bearer ${ENVLY_TOKEN}" \\',
        '        "${ENVLY_API_URL}" > .env',
    ].join('\n');
});

const copyFromExisting = computed({
    get: () => environmentForm.creation_mode === 'copy',
    set: (shouldCopy: boolean) => {
        environmentForm.creation_mode = shouldCopy ? 'copy' : 'empty';
    },
});

watch(activeEnvironment, (environment) => {
    contentForm.content = environment?.content ?? '';
});

watch(
    () => props.project.environments.map((environment) => environment.id),
    (environmentIds) => {
        if (!environmentIds.includes(selectedEnvironmentId.value)) {
            selectedEnvironmentId.value = environmentIds[0] ?? 0;
        }
    },
);

watch(
    () => environmentForm.creation_mode,
    (creationMode) => {
        if (creationMode === 'empty') {
            environmentForm.source_environment_id = null;

            return;
        }

        environmentForm.source_environment_id ??= activeEnvironment.value?.id ?? props.project.environments[0]?.id ?? null;
    },
);

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

const goToProjects = () => {
    router.visit(route('projects.index'));
};

const createEnvironment = () => {
    environmentForm.post(route('projects.environments.store', props.project.identifier), {
        preserveScroll: true,
        onSuccess: () => closeCreateEnvironmentModal(),
        onError: () => environmentNameInput.value?.focus(),
    });
};

const openCreateEnvironmentModal = () => {
    creatingEnvironment.value = true;

    nextTick(() => environmentNameInput.value?.focus());
};

const closeCreateEnvironmentModal = () => {
    creatingEnvironment.value = false;
    environmentForm.clearErrors();
    environmentForm.reset();
};

const openDeleteEnvironmentModal = () => {
    if (!activeEnvironment.value || !canDeleteEnvironment.value) {
        return;
    }

    selectedEnvironmentForDeletion.value = activeEnvironment.value;
    confirmingEnvironmentDeletion.value = true;

    nextTick(() => deleteEnvironmentNameInput.value?.focus());
};

const closeDeleteEnvironmentModal = () => {
    confirmingEnvironmentDeletion.value = false;
    selectedEnvironmentForDeletion.value = null;
    deleteEnvironmentForm.clearErrors();
    deleteEnvironmentForm.reset();
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

const openUsageExamplesModal = () => {
    showingUsageExamples.value = true;
};

const closeUsageExamplesModal = () => {
    showingUsageExamples.value = false;
};

const runGithubDeploy = () => {
    deployForm.post(route('projects.github.deploy', props.project.identifier), {
        preserveScroll: true,
    });
};

const openHistoryDetailsModal = (version: EnvironmentVersion) => {
    selectedHistoryVersion.value = version;
};

const closeHistoryDetailsModal = () => {
    selectedHistoryVersion.value = null;
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

const deleteEnvironment = () => {
    if (!selectedEnvironmentForDeletion.value) {
        return;
    }

    deleteEnvironmentForm.delete(
        route('projects.environments.destroy', [props.project.identifier, selectedEnvironmentForDeletion.value.id]),
        {
            preserveScroll: true,
            onSuccess: () => closeDeleteEnvironmentModal(),
            onError: () => deleteEnvironmentNameInput.value?.focus(),
            onFinish: () => deleteEnvironmentForm.reset('name'),
        },
    );
};

const deleteConfirmationLabel = computed(() => {
    if (!selectedEnvironmentForDeletion.value) {
        return t('environments.name');
    }

    return t('environments.delete_confirm_label').replace(':name', selectedEnvironmentForDeletion.value.name);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="project.name" />

        <v-card class="env-card pa-5 mb-5" rounded="xl">
            <div class="ops-kicker mb-2">GITHUB / DEPLOY</div>
            <h2 class="text-h5 font-weight-black mb-2">{{ t('projects.github_section_title') }}</h2>
            <p class="ops-muted mb-5">{{ t('projects.github_section_hint') }}</p>

            <v-alert v-if="!github.connected" type="info" variant="tonal" class="mb-4">
                {{ t('projects.github_connect_hint') }}
            </v-alert>

            <v-alert v-else-if="!project.github_repository_full_name || !project.github_workflow_id" type="warning" variant="tonal" class="mb-4">
                {{ t('projects.github_not_configured_hint') }}
            </v-alert>

            <div v-else class="d-flex flex-wrap ga-3 mb-4">
                <v-chip color="secondary" variant="tonal">
                    {{ project.github_repository_full_name }}
                </v-chip>
                <v-chip color="primary" variant="tonal">
                    {{ t('projects.github_current_workflow') }}: {{ project.github_workflow_name }}
                </v-chip>
                <v-chip v-if="project.github_deploy_ref" variant="outlined">
                    ref: {{ project.github_deploy_ref }}
                </v-chip>
            </div>

            <div class="d-flex flex-wrap ga-3">
                <v-btn
                    v-if="!github.connected"
                    :href="route('profile.edit')"
                    class="env-action-btn"
                    variant="flat"
                    rounded="lg"
                    prepend-icon="mdi-account-cog-outline"
                >
                    {{ t('projects.github_open_profile') }}
                </v-btn>

                <v-btn
                    v-else
                    :href="route('projects.github.edit', project.identifier)"
                    class="env-action-btn"
                    variant="flat"
                    rounded="lg"
                    prepend-icon="mdi-cog-outline"
                >
                    {{ t('projects.github_open_configuration') }}
                </v-btn>

                <v-btn
                    class="env-action-btn"
                    variant="flat"
                    rounded="lg"
                    prepend-icon="mdi-rocket-launch-outline"
                    :loading="deployForm.processing"
                    :disabled="!canRunGithubDeploy"
                    @click="runGithubDeploy"
                >
                    {{ t('projects.github_run_deploy') }}
                </v-btn>
            </div>
        </v-card>

        <v-row>
            <v-col cols="12" lg="3">
                <v-card class="env-card pa-5" rounded="xl">
                    <v-btn variant="text" rounded="lg" size="small" prepend-icon="mdi-arrow-left" class="mb-4 px-0" @click="goToProjects">
                        {{ t('environments.back_to_projects') }}
                    </v-btn>

                    <div class="text-overline text-secondary font-weight-bold">{{ t('projects.identifier') }}</div>
                    <h1 class="text-h4 font-weight-black mb-2">{{ project.name }}</h1>
                    <v-chip color="primary" variant="tonal">{{ project.identifier }}</v-chip>

                    <v-divider class="my-5" />

                    <div class="d-flex align-center justify-space-between mb-3 ga-3">
                        <div class="text-subtitle-1 font-weight-bold">{{ t('environments.title') }}</div>
                        <v-btn
                            icon="mdi-plus"
                            size="small"
                            color="primary"
                            variant="tonal"
                            rounded="xl"
                            @click="openCreateEnvironmentModal"
                        />
                    </div>
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

                    <div class="environment-danger-zone mt-5">
                        <div>
                            <div class="text-caption font-weight-bold text-error">{{ t('environments.delete') }}</div>
                            <p class="text-caption text-medium-emphasis mb-0">
                                {{ canDeleteEnvironment ? t('environments.delete_hint') : t('environments.delete_disabled_hint') }}
                            </p>
                        </div>
                        <v-btn
                            color="error"
                            variant="tonal"
                            rounded="lg"
                            size="small"
                            prepend-icon="mdi-trash-can-outline"
                            :disabled="!canDeleteEnvironment"
                            @click="openDeleteEnvironmentModal"
                        >
                            {{ t('environments.delete') }}
                        </v-btn>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" lg="3">
                <v-card v-if="activeEnvironment" class="env-card pa-5 mb-5" rounded="xl">
                    <div class="text-subtitle-1 font-weight-bold mb-2">{{ t('environments.token') }}</div>
                    <v-text-field :model-value="activeEnvironment.access_token" readonly variant="outlined" density="compact" />
                    <div class="text-caption text-medium-emphasis mb-2">{{ t('environments.api_url') }}</div>
                    <v-text-field :model-value="apiUrl" readonly variant="outlined" density="compact" prepend-inner-icon="mdi-link-variant" />
                    <p class="text-body-2 mb-4">{{ t('projects.api_hint') }}</p>

                    <v-alert type="info" variant="tonal" density="comfortable" class="mb-4">
                        {{ t('environments.token_security_hint') }}
                    </v-alert>
                    <v-btn class="mb-4" variant="tonal" rounded="xl" block prepend-icon="mdi-book-open-variant" @click="openUsageExamplesModal">
                        {{ t('environments.usage_examples') }}
                    </v-btn>

                    <v-btn class="env-action-btn" variant="flat" rounded="xl" block prepend-icon="mdi-lock-reset" @click="openRegenerateTokenModal">
                        {{ t('environments.regenerate_token') }}
                    </v-btn>
                </v-card>

                <v-card v-if="activeEnvironment" class="env-card pa-5" rounded="xl">
                    <div class="text-subtitle-1 font-weight-bold mb-3">{{ t('environments.history') }}</div>
                    <div class="d-flex flex-column ga-3">
                        <button
                            v-for="version in activeEnvironment.versions"
                            :key="version.id"
                            type="button"
                            class="history-entry"
                            @click="openHistoryDetailsModal(version)"
                        >
                            <div class="w-100">
                                <div class="font-weight-bold">{{ version.summary }}</div>
                                <div class="text-caption text-medium-emphasis mt-1">
                                    +{{ version.added_lines }} {{ t('history.added') }}, -{{ version.removed_lines }} {{ t('history.removed') }}
                                </div>
                                <div class="text-caption mt-1">
                                    {{ t('history.changed_by') }}: {{ version.creator?.name ?? '—' }}
                                    <span v-if="version.created_at">· {{ formatDateTime(version.created_at) }}</span>
                                </div>
                            </div>
                            <v-icon icon="mdi-open-in-new" size="18" />
                        </button>
                    </div>
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
                    <v-btn class="env-action-btn" variant="flat" rounded="lg" :loading="tokenForm.processing" @click="regenerateToken">
                        {{ t('environments.regenerate_token_confirm_action') }}
                    </v-btn>
                </div>
            </div>
        </Modal>

        <Modal :show="showingUsageExamples" max-width="xl" @close="closeUsageExamplesModal">
            <div class="pa-6">
                <div class="ops-kicker mb-2">{{ t('environments.api_url') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ t('environments.usage_examples') }}</h2>
                <p class="ops-muted mb-6">{{ t('projects.api_hint') }}</p>

                <v-expansion-panels variant="accordion" elevation="0" class="api-examples">
                    <v-expansion-panel rounded="lg">
                        <v-expansion-panel-title>
                            <v-icon icon="mdi-console-line" class="mr-2" />
                            {{ t('environments.example_curl') }}
                        </v-expansion-panel-title>
                        <v-expansion-panel-text>
                            <pre class="api-example-code">{{ curlExample }}</pre>
                        </v-expansion-panel-text>
                    </v-expansion-panel>

                    <v-expansion-panel rounded="lg">
                        <v-expansion-panel-title>
                            <v-icon icon="mdi-github" class="mr-2" />
                            {{ t('environments.example_github_actions') }}
                        </v-expansion-panel-title>
                        <v-expansion-panel-text>
                            <pre class="api-example-code">{{ githubActionsExample }}</pre>
                        </v-expansion-panel-text>
                    </v-expansion-panel>
                </v-expansion-panels>

                <div class="d-flex justify-end mt-6">
                    <v-btn variant="text" rounded="lg" @click="closeUsageExamplesModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                </div>
            </div>
        </Modal>

        <Modal :show="selectedHistoryVersion !== null" max-width="xl" @close="closeHistoryDetailsModal">
            <div v-if="selectedHistoryVersion" class="pa-6">
                <div class="ops-kicker mb-2">{{ t('environments.history') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ selectedHistoryVersion.summary }}</h2>
                <div class="ops-muted mb-6">
                    +{{ selectedHistoryVersion.added_lines }} {{ t('history.added') }},
                    -{{ selectedHistoryVersion.removed_lines }} {{ t('history.removed') }}
                    <span v-if="selectedHistoryVersion.created_at">· {{ formatDateTime(selectedHistoryVersion.created_at) }}</span>
                </div>

                <template v-if="selectedHistoryVersion.has_content_changes">
                    <div class="history-detail-grid">
                        <div>
                            <div class="text-caption text-medium-emphasis mb-2">{{ t('history.before') }}</div>
                            <pre class="history-content">{{ selectedHistoryVersion.previous_content || t('history.no_previous_content') }}</pre>
                        </div>
                        <div>
                            <div class="text-caption text-medium-emphasis mb-2">{{ t('history.after') }}</div>
                            <pre class="history-content">{{ selectedHistoryVersion.content }}</pre>
                        </div>
                    </div>
                </template>

                <v-alert v-else type="info" variant="tonal" density="comfortable">
                    {{ t('history.no_content_changes') }} {{ t('history.token_regenerated_note') }}
                </v-alert>

                <div class="d-flex justify-end mt-6">
                    <v-btn variant="text" rounded="lg" @click="closeHistoryDetailsModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                </div>
            </div>
        </Modal>

        <Modal :show="creatingEnvironment" max-width="md" @close="closeCreateEnvironmentModal">
            <div class="pa-6">
                <div class="ops-kicker mb-2">{{ t('environments.new') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ t('environments.create') }}</h2>
                <p class="ops-muted mb-6">{{ t('environments.create_hint') }}</p>

                <v-text-field
                    id="environment_name"
                    ref="environmentNameInput"
                    v-model="environmentForm.name"
                    :label="t('environments.name')"
                    :error-messages="environmentForm.errors.name"
                    autocomplete="off"
                    variant="outlined"
                    @keyup.enter="createEnvironment"
                />

                <v-checkbox
                    v-model="copyFromExisting"
                    :label="t('environments.copy_source_toggle')"
                    :error-messages="environmentForm.errors.creation_mode"
                    color="primary"
                    density="comfortable"
                    hide-details="auto"
                />

                <v-select
                    v-if="environmentForm.creation_mode === 'copy'"
                    v-model="environmentForm.source_environment_id"
                    class="copy-source-select"
                    :items="sourceEnvironmentOptions"
                    :label="t('environments.copy_source')"
                    :hint="t('environments.copy_source_hint')"
                    :error-messages="environmentForm.errors.source_environment_id"
                    :menu-props="selectMenuProps"
                    persistent-hint
                    variant="outlined"
                />

                <div class="create-environment-actions d-flex justify-end ga-3 mt-2">
                    <v-btn variant="text" rounded="lg" @click="closeCreateEnvironmentModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                    <v-btn class="env-action-btn" variant="flat" rounded="lg" :loading="environmentForm.processing" @click="createEnvironment">
                        {{ t('environments.create') }}
                    </v-btn>
                </div>
            </div>
        </Modal>

        <Modal :show="confirmingEnvironmentDeletion" max-width="md" @close="closeDeleteEnvironmentModal">
            <div v-if="selectedEnvironmentForDeletion" class="pa-6">
                <div class="ops-kicker mb-2">{{ t('environments.delete') }}</div>
                <h2 class="text-h5 font-weight-black mb-2">{{ t('environments.delete_confirm_title') }}</h2>
                <p class="ops-muted mb-6">{{ t('environments.delete_confirm_body') }}</p>

                <v-text-field
                    id="delete_environment_name"
                    ref="deleteEnvironmentNameInput"
                    v-model="deleteEnvironmentForm.name"
                    :label="deleteConfirmationLabel"
                    :error-messages="deleteEnvironmentForm.errors.name"
                    autocomplete="off"
                    variant="outlined"
                    @keyup.enter="deleteEnvironment"
                />

                <div class="d-flex justify-end ga-3 mt-2">
                    <v-btn variant="text" rounded="lg" @click="closeDeleteEnvironmentModal">
                        {{ t('profile.cancel') }}
                    </v-btn>
                    <v-btn color="error" variant="flat" rounded="lg" :loading="deleteEnvironmentForm.processing" @click="deleteEnvironment">
                        {{ t('environments.delete_confirm_action') }}
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

.environment-danger-zone {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border: 1px solid rgba(var(--v-theme-error), 0.26);
    border-radius: 1rem;
    background: rgba(var(--v-theme-error), 0.06);
}

.copy-source-select {
    position: relative;
    z-index: 20;
}

.copy-source-select :deep(.v-overlay__content),
:global(.env-select-menu) {
    z-index: 24000 !important;
}

.create-environment-actions {
    position: relative;
    z-index: 1;
}

.history-entry {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    color: inherit;
    text-align: left;
    border: 1px solid rgba(15, 143, 114, 0.16);
    border-radius: 0.85rem;
    background: rgba(15, 143, 114, 0.04);
    transition: border-color 160ms ease, background 160ms ease, transform 160ms ease;
}

.history-entry:hover {
    border-color: rgba(89, 243, 183, 0.34);
    background: rgba(89, 243, 183, 0.08);
    transform: translateY(-1px);
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

.api-examples :deep(.v-expansion-panel-title) {
    min-height: 48px;
    padding: 0.7rem 0.9rem;
}

.api-examples :deep(.v-expansion-panel-text__wrapper) {
    padding: 0 0.9rem 0.9rem;
}

.api-example-code {
    margin: 0;
    padding: 0.85rem 0.9rem;
    border-radius: 0.75rem;
    background: rgba(15, 143, 114, 0.08);
    border: 1px solid rgba(15, 143, 114, 0.16);
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 0.78rem;
    line-height: 1.45;
}

@media (min-width: 960px) {
    .history-detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
