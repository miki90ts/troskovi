<script setup lang="ts">
import { computed } from 'vue';
import {
    Download,
    Loader2,
    Plus,
    Search,
    SlidersHorizontal,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { t } from '@/lib/i18n';

const props = withDefaults(
    defineProps<{
        search: string;
        searchPlaceholder: string;
        showFilters?: boolean;
        activeFiltersCount?: number;
        exportingPdf?: boolean;
        createLabel: string;
        showExport?: boolean;
    }>(),
    {
        showFilters: false,
        activeFiltersCount: 0,
        exportingPdf: false,
        showExport: true,
    },
);

const emit = defineEmits<{
    'update:search': [value: string];
    toggleFilters: [];
    export: [];
    create: [];
}>();

const searchModel = computed({
    get: () => props.search,
    set: (value: string) => emit('update:search', value),
});
</script>

<template>
    <div class="relative min-w-0 flex-1 sm:w-72">
        <Search
            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="searchModel"
            :placeholder="searchPlaceholder"
            class="h-11 rounded-2xl border-border/60 bg-background pl-9"
        />
    </div>

    <Button
        variant="outline"
        class="h-11 rounded-2xl border-border/60 px-4"
        @click="emit('toggleFilters')"
    >
        <SlidersHorizontal class="mr-2 h-4 w-4" />
        {{ t('common.actions.filters') }}
        <span
            v-if="activeFiltersCount"
            class="ml-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-primary/10 px-2 text-xs font-semibold text-primary"
        >
            {{ activeFiltersCount }}
        </span>
    </Button>

    <Button
        v-if="showExport"
        variant="outline"
        class="h-11 rounded-2xl border-border/60 bg-red-500 px-4 text-white"
        :disabled="exportingPdf"
        @click="emit('export')"
    >
        <Loader2 v-if="exportingPdf" class="mr-2 h-4 w-4 animate-spin" />
        <Download v-else class="mr-2 h-4 w-4" />
        PDF
    </Button>

    <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
        <Plus class="mr-2 h-4 w-4" />
        {{ createLabel }}
    </Button>
</template>
