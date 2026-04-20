<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import type { Project } from '@/types';

const props = defineProps<{ projects: Project[] }>();
const { t } = useTranslations();

const form = useForm({ name: '' });
const submit = () => form.post(route('projects.store'), { onSuccess: () => form.reset() });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="t('projects.title')" />

        <v-row>
            <v-col cols="12" md="5">
                <v-card class="env-card pa-6" rounded="xl">
                    <div class="text-overline text-secondary font-weight-bold">{{ t('projects.new') }}</div>
                    <h1 class="text-h3 font-weight-black mb-5">{{ t('projects.title') }}</h1>

                    <form @submit.prevent="submit">
                        <v-text-field
                            v-model="form.name"
                            :label="t('projects.name')"
                            :error-messages="form.errors.name"
                            variant="outlined"
                            autofocus
                        />
                        <v-btn type="submit" color="primary" rounded="xl" size="large" :loading="form.processing" block>
                            {{ t('projects.create') }}
                        </v-btn>
                    </form>
                </v-card>
            </v-col>

            <v-col cols="12" md="7">
                <v-card v-if="props.projects.length === 0" class="pa-8" rounded="xl" variant="tonal">
                    {{ t('projects.empty') }}
                </v-card>

                <div v-else class="d-flex flex-column ga-4">
                    <v-card v-for="project in props.projects" :key="project.id" class="pa-5" rounded="xl">
                        <div class="d-flex align-center justify-space-between ga-4">
                            <div>
                                <h2 class="text-h5 font-weight-black">{{ project.name }}</h2>
                                <div class="text-medium-emphasis">{{ t('projects.identifier') }}: {{ project.identifier }}</div>
                            </div>
                            <v-btn :href="route('projects.show', project.identifier)" color="primary" rounded="xl">
                                {{ t('projects.open') }}
                            </v-btn>
                        </div>
                    </v-card>
                </div>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
