<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Project } from '@/types';
import axios from 'axios';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

type GithubRepository = {
    id: number;
    full_name: string;
    name: string;
    default_branch: string;
    private: boolean;
    html_url: string;
};

type GithubWorkflow = {
    id: string;
    name: string;
    path: string;
    state: string;
};

const props = defineProps<{
    project: Project;
    github: {
        connected: boolean;
    };
}>();

const { t } = useTranslations();

const repositories = ref<GithubRepository[]>([]);
const workflows = ref<GithubWorkflow[]>([]);
const loadingRepositories = ref(false);
const loadingWorkflows = ref(false);
const githubError = ref<string | null>(null);

const form = useForm({
    repository_full_name: props.project.github_repository_full_name ?? '',
    workflow_id: props.project.github_workflow_id ?? '',
    deploy_ref: props.project.github_deploy_ref ?? '',
});

const repositoryOptions = computed(() =>
    repositories.value.map((repository) => ({
        title: repository.full_name,
        value: repository.full_name,
    })),
);

const workflowOptions = computed(() =>
    workflows.value.map((workflow) => ({
        title: `${workflow.name} (${workflow.path})`,
        value: workflow.id,
    })),
);

const selectedRepository = computed(() =>
    repositories.value.find((repository) => repository.full_name === form.repository_full_name),
);

const fetchRepositories = async (): Promise<void> => {
    loadingRepositories.value = true;
    githubError.value = null;

    try {
        const response = await axios.get(route('projects.github.repositories', props.project.identifier));

        repositories.value = Array.isArray(response.data?.repositories) ? response.data.repositories : [];
    } catch (error: any) {
        repositories.value = [];
        githubError.value = error?.response?.data?.message ?? t('projects.github_api_error');
    } finally {
        loadingRepositories.value = false;
    }
};

const fetchWorkflows = async (repositoryFullName: string): Promise<void> => {
    if (!repositoryFullName) {
        workflows.value = [];

        return;
    }

    loadingWorkflows.value = true;
    githubError.value = null;

    try {
        const response = await axios.get(route('projects.github.workflows', props.project.identifier), {
            params: {
                repository: repositoryFullName,
            },
        });

        workflows.value = Array.isArray(response.data?.workflows) ? response.data.workflows : [];
    } catch (error: any) {
        workflows.value = [];
        githubError.value = error?.response?.data?.message ?? t('projects.github_api_error');
    } finally {
        loadingWorkflows.value = false;
    }
};

const saveConfiguration = (): void => {
    form.put(route('projects.github.update', props.project.identifier), {
        preserveScroll: true,
    });
};

watch(
    () => form.repository_full_name,
    (repositoryFullName, previousRepositoryFullName) => {
        if (repositoryFullName === previousRepositoryFullName) {
            return;
        }

        form.workflow_id = '';
        form.deploy_ref = selectedRepository.value?.default_branch ?? '';
        void fetchWorkflows(repositoryFullName);
    },
);

onMounted(async () => {
    if (!props.github.connected) {
        return;
    }

    await fetchRepositories();

    if (form.repository_full_name) {
        await fetchWorkflows(form.repository_full_name);

        if (!form.deploy_ref) {
            form.deploy_ref = selectedRepository.value?.default_branch ?? '';
        }
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${project.name} · ${t('projects.github_configuration_page_title')}`" />

        <v-card class="env-card pa-6" rounded="xl">
            <div class="d-flex align-center justify-space-between mb-5 ga-3 flex-wrap">
                <div>
                    <div class="ops-kicker mb-2">GITHUB / DEPLOY / CONFIG</div>
                    <h1 class="text-h4 font-weight-black mb-2">{{ t('projects.github_configuration_page_title') }}</h1>
                    <p class="ops-muted mb-0">{{ t('projects.github_configuration_page_hint') }}</p>
                </div>

                <v-btn :href="route('projects.show', project.identifier)" variant="tonal" rounded="lg" prepend-icon="mdi-arrow-left">
                    {{ t('environments.back_to_projects') }}
                </v-btn>
            </div>

            <v-alert v-if="!github.connected" type="info" variant="tonal" class="mb-4">
                {{ t('projects.github_connect_hint') }}
                <div class="mt-3">
                    <v-btn :href="route('profile.edit')" class="env-action-btn" variant="flat" rounded="lg">
                        {{ t('projects.github_open_profile') }}
                    </v-btn>
                </div>
            </v-alert>

            <template v-else>
                <v-alert v-if="githubError" type="error" variant="tonal" class="mb-4">
                    {{ githubError }}
                </v-alert>

                <v-skeleton-loader v-if="loadingRepositories" type="article, article" class="mb-4" />

                <v-alert v-else-if="repositories.length === 0" type="warning" variant="tonal" class="mb-4">
                    {{ t('projects.github_no_repositories') }}
                </v-alert>

                <v-row>
                    <v-col cols="12" md="5">
                        <v-autocomplete
                            v-model="form.repository_full_name"
                            :items="repositoryOptions"
                            :label="t('projects.github_repository_label')"
                            :hint="t('projects.github_repository_hint')"
                            :error-messages="form.errors.repository_full_name"
                            :disabled="loadingRepositories || repositories.length === 0"
                            clearable
                            :no-data-text="t('projects.github_no_results')"
                            variant="outlined"
                            persistent-hint
                        />
                    </v-col>

                    <v-col cols="12" md="4">
                        <v-autocomplete
                            v-model="form.workflow_id"
                            :items="workflowOptions"
                            :label="t('projects.github_workflow_label')"
                            :hint="t('projects.github_workflow_hint')"
                            :error-messages="form.errors.workflow_id"
                            :loading="loadingWorkflows"
                            :disabled="!form.repository_full_name"
                            clearable
                            :no-data-text="t('projects.github_no_results')"
                            variant="outlined"
                            persistent-hint
                        />
                    </v-col>

                    <v-col cols="12" md="3">
                        <v-text-field
                            v-model="form.deploy_ref"
                            :label="t('projects.github_ref_label')"
                            :hint="t('projects.github_ref_hint')"
                            :error-messages="form.errors.deploy_ref"
                            variant="outlined"
                            persistent-hint
                        />
                    </v-col>
                </v-row>

                <div class="d-flex flex-wrap ga-3 mt-3">
                    <v-btn
                        class="env-action-btn"
                        variant="flat"
                        rounded="lg"
                        prepend-icon="mdi-content-save-outline"
                        :loading="form.processing"
                        :disabled="!form.repository_full_name || !form.workflow_id"
                        @click="saveConfiguration"
                    >
                        {{ t('projects.github_save_configuration') }}
                    </v-btn>
                </div>
            </template>
        </v-card>
    </AuthenticatedLayout>
</template>
