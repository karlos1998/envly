<script setup lang="ts">
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

interface EnvRow {
    key: string;
    value: string;
}

const model = defineModel<string>({ required: true });
const { t } = useTranslations();

const rows = computed<EnvRow[]>({
    get: () =>
        model.value
            .split('\n')
            .filter((line) => line.trim() !== '' && !line.trim().startsWith('#'))
            .map((line) => {
                const [key, ...value] = line.split('=');

                return { key: key ?? '', value: value.join('=') };
            }),
    set: (value) => {
        model.value = value.map((row) => `${row.key}=${row.value}`).join('\n');
    },
});

const updateRow = (index: number, key: keyof EnvRow, value: string) => {
    const nextRows = [...rows.value];
    nextRows[index] = { ...nextRows[index], [key]: value };
    rows.value = nextRows;
};

const addRow = () => {
    rows.value = [...rows.value, { key: '', value: '' }];
};

const removeRow = (index: number) => {
    rows.value = rows.value.filter((_, rowIndex) => rowIndex !== index);
};
</script>

<template>
    <div class="d-flex flex-column ga-3">
        <v-row v-for="(row, index) in rows" :key="index" dense>
            <v-col cols="12" md="5">
                <v-text-field
                    :model-value="row.key"
                    :label="t('environments.key')"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    @update:model-value="updateRow(index, 'key', String($event))"
                />
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field
                    :model-value="row.value"
                    :label="t('environments.value')"
                    variant="outlined"
                    density="comfortable"
                    hide-details
                    @update:model-value="updateRow(index, 'value', String($event))"
                />
            </v-col>
            <v-col cols="12" md="1" class="d-flex align-center">
                <v-btn icon="mdi-delete" variant="text" color="error" @click="removeRow(index)" />
            </v-col>
        </v-row>

        <v-btn prepend-icon="mdi-plus" variant="tonal" color="primary" @click="addRow">
            {{ t('environments.add_row') }}
        </v-btn>
    </div>
</template>
