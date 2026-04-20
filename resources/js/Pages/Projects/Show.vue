<script setup lang="ts">
import EnvRowsEditor from '@/Components/Env/EnvRowsEditor.vue';
import EnvTextEditor from '@/Components/Env/EnvTextEditor.vue';
import { useTranslations } from '@/composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Project, ProjectEnvironment } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{ project: Project }>();
const { t } = useTranslations();

const selectedEnvironmentId = ref<number>(props.project.environments[0]?.id ?? 0);
const editorMode = ref<'text' | 'rows'>('text');

const activeEnvironment = computed<ProjectEnvironment | undefined>(() =>
    props.project.environments.find((environment) => environment.id === selectedEnvironmentId.value),
);

const contentForm = useForm({ content: activeEnvironment.value?.content ?? '' });
const environmentForm = useForm({ name: '', content: '' });

const apiUrl = computed(() => {
    if (!activeEnvironment.value) {
        return '';
    }

    return `/api/env/${props.project.identifier}/${activeEnvironment.value.access_token}`;
});

watch(activeEnvironment, (environment) => {
    contentForm.content = environment?.content ?? '';
});

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

const regenerateToken = () => {
    if (!activeEnvironment.value) {
        return;
    }

    router.post(route('projects.environments.token', [props.project.identifier, activeEnvironment.value.id]), {}, {
        preserveScroll: true,
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
                            <v-list-item-subtitle>{{ environment.line_count }} {{ t('environments.lines') }}</v-list-item-subtitle>
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
                        <v-btn-toggle v-model="editorMode" color="primary" rounded="xl" mandatory>
                            <v-btn value="text">{{ t('environments.text_mode') }}</v-btn>
                            <v-btn value="rows">{{ t('environments.rows_mode') }}</v-btn>
                        </v-btn-toggle>
                    </div>

                    <EnvTextEditor v-if="editorMode === 'text'" v-model="contentForm.content" />
                    <EnvRowsEditor v-else v-model="contentForm.content" />

                    <v-alert v-if="contentForm.errors.content" class="mt-4" type="error" variant="tonal">
                        {{ contentForm.errors.content }}
                    </v-alert>

                    <div class="d-flex justify-end mt-5">
                        <v-btn color="primary" size="large" rounded="xl" :loading="contentForm.processing" @click="saveContent">
                            {{ t('environments.save') }}
                        </v-btn>
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
                    <v-btn color="secondary" variant="tonal" rounded="xl" block @click="regenerateToken">
                        {{ t('environments.regenerate_token') }}
                    </v-btn>
                </v-card>

                <v-card v-if="activeEnvironment" class="env-card pa-5" rounded="xl">
                    <div class="text-subtitle-1 font-weight-bold mb-3">{{ t('environments.history') }}</div>
                    <v-timeline side="end" density="compact">
                        <v-timeline-item
                            v-for="version in activeEnvironment.versions"
                            :key="version.id"
                            dot-color="primary"
                            size="small"
                        >
                            <div class="font-weight-bold">{{ version.summary }}</div>
                            <div class="text-caption text-medium-emphasis">
                                +{{ version.added_lines }} {{ t('history.added') }}, -{{ version.removed_lines }} {{ t('history.removed') }}
                            </div>
                            <div class="text-caption">{{ version.creator?.name }}</div>
                        </v-timeline-item>
                    </v-timeline>
                </v-card>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
