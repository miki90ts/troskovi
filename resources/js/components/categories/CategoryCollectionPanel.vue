<script setup lang="ts">
import { computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { t } from '@/lib/i18n';
import type { Category } from '@/types';
import CategoryCard from './CategoryCard.vue';

const props = defineProps<{
    categories: Category[];
    tab: 'expense' | 'income';
}>();

const emit = defineEmits<{
    create: [];
    edit: [category: Category];
    delete: [category: Category];
}>();

const systemCount = computed(
    () => props.categories.filter((category) => category.is_system).length,
);
const editableCount = computed(
    () => props.categories.length - systemCount.value,
);
const sectionLabel = computed(() =>
    props.tab === 'expense'
        ? t('finance.categories.expenseCollection')
        : t('finance.categories.incomeCollection'),
);
const sectionDescription = computed(() =>
    props.tab === 'expense'
        ? t('finance.categories.expenseSectionDescription')
        : t('finance.categories.incomeSectionDescription'),
);
const emptyTitle = computed(() =>
    props.tab === 'expense'
        ? t('finance.categories.emptyExpenseTitle')
        : t('finance.categories.emptyIncomeTitle'),
);
const emptyDescription = computed(() =>
    props.tab === 'expense'
        ? t('finance.categories.emptyExpenseDescription')
        : t('finance.categories.emptyIncomeDescription'),
);
</script>

<template>
    <div>
        <div
            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ sectionLabel }}
                </p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight">
                    {{ sectionDescription }}
                </h3>
            </div>
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>
                    {{
                        t('finance.categories.totalCount', {
                            count: categories.length,
                        })
                    }}
                </span>
                <span class="hidden h-1 w-1 rounded-full bg-border sm:block" />
                <span>
                    {{ editableCount }}
                    {{
                        t('finance.categories.editableCount', {
                            count: editableCount,
                        })
                    }}
                </span>
            </div>
        </div>

        <div v-if="categories.length === 0">
            <EmptyState :title="emptyTitle" :description="emptyDescription">
                <Button class="rounded-2xl px-5" @click="emit('create')">
                    {{ t('finance.categories.add') }}
                </Button>
            </EmptyState>
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <CategoryCard
                v-for="category in categories"
                :key="category.id"
                :category="category"
                @edit="emit('edit', $event)"
                @delete="emit('delete', $event)"
            />
        </div>
    </div>
</template>
