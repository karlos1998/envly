<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { EnvTemplateOption, Project } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    filters: {
        search: string;
    };
    envTemplateOptions: EnvTemplateOption[];
    projects: Project[];
}>();

const { t } = useTranslations();
const search = ref(props.filters.search);
const form = useForm<{ name: string; template: string | null }>({ name: '', template: null });

const submit = () => form.post(route('projects.store'), { onSuccess: () => form.reset() });

const searchProjects = () => {
    router.get(
        route('projects.index'),
        { search: search.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const clearSearch = () => {
    search.value = '';
    searchProjects();
};
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
                        <v-select
                            v-model="form.template"
                            :items="props.envTemplateOptions"
                            :label="t('projects.template')"
                            :hint="t('projects.template_hint')"
                            :placeholder="t('projects.template_empty')"
                            :error-messages="form.errors.template"
                            item-title="label"
                            item-value="value"
                            persistent-hint
                            clearable
                            variant="outlined"
                        >
                            <template #item="{ props: itemProps, item }">
                                <v-list-item v-bind="itemProps">
                                    <v-list-item-subtitle>
                                        {{ item.description }}
                                    </v-list-item-subtitle>
                                </v-list-item>
                            </template>
                            <template #selection="{ item }">
                                {{ item.label }}
                            </template>
                        </v-select>
                        <v-btn type="submit" color="primary" rounded="xl" size="large" :loading="form.processing" block>
                            {{ t('projects.create') }}
                        </v-btn>
                    </form>
                </v-card>
            </v-col>

            <v-col cols="12" md="7">
                <v-card class="env-card project-list-shell pa-6" rounded="xl">
                    <div class="project-list-header">
                        <div>
                            <div class="ops-kicker mb-2">{{ t('projects.list') }}</div>
                            <h2 class="text-h4 font-weight-black mb-2">{{ t('projects.list') }}</h2>
                            <p class="ops-muted mb-0">{{ t('projects.list_hint') }}</p>
                        </div>

                        <form class="project-search" @submit.prevent="searchProjects">
                            <v-text-field
                                v-model="search"
                                :placeholder="t('projects.search_placeholder')"
                                :aria-label="t('projects.search')"
                                append-inner-icon="mdi-magnify"
                                hide-details
                                variant="outlined"
                            />
                            <v-btn type="submit" color="primary" rounded="lg">
                                {{ t('projects.search') }}
                            </v-btn>
                            <v-btn v-if="search" variant="text" color="secondary" rounded="lg" @click="clearSearch">
                                {{ t('projects.clear_search') }}
                            </v-btn>
                        </form>
                    </div>

                    <v-card v-if="props.projects.length === 0" class="empty-projects pa-6 mt-6" rounded="lg" variant="tonal">
                        {{ props.filters.search ? t('projects.empty_search') : t('projects.empty') }}
                    </v-card>

                    <div v-else class="d-flex flex-column ga-4 mt-6">
                        <v-card v-for="project in props.projects" :key="project.id" class="project-card pa-5" rounded="lg">
                            <div class="d-flex align-center justify-space-between ga-4 flex-wrap">
                                <div>
                                    <h3 class="text-h5 font-weight-black">{{ project.name }}</h3>
                                    <div class="ops-muted">{{ t('projects.identifier') }}: {{ project.identifier }}</div>
                                </div>
                                <v-btn :href="route('projects.show', project.identifier)" color="primary" rounded="lg" variant="flat">
                                    {{ t('projects.open') }}
                                </v-btn>
                            </div>
                        </v-card>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>

<style scoped>
.project-list-shell {
    min-height: 370px;
}

.project-list-header {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(280px, 390px);
    gap: 24px;
    align-items: start;
}

.project-search {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
}

.project-search .v-btn:last-child {
    grid-column: 1 / -1;
    justify-self: end;
}

.project-card,
.empty-projects {
    background: color-mix(in srgb, var(--envly-background) 62%, transparent) !important;
    border: 1px solid color-mix(in srgb, var(--envly-primary) 12%, transparent) !important;
}

@media (max-width: 980px) {
    .project-list-header {
        grid-template-columns: 1fr;
    }

    .project-search {
        grid-template-columns: 1fr;
    }

    .project-search .v-btn:last-child {
        justify-self: stretch;
    }
}
</style>
