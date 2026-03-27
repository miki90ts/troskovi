<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import BudgetTargetCard from '@/components/budgets/BudgetTargetCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import LoadingSkeleton from '@/components/LoadingSkeleton.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { t } from '@/lib/i18n';
import type {
    BudgetPeriodSection,
    SpendingTarget,
    SpendingTargetPeriod,
    StatusFilter,
} from '@/types';

defineProps<{
    loading: boolean;
    hasTargets: boolean;
    activePeriodTab: SpendingTargetPeriod;
    periodSections: BudgetPeriodSection[];
    statusFilter: StatusFilter;
}>();

const emit = defineEmits<{
    'update:activePeriodTab': [value: SpendingTargetPeriod];
    create: [period?: SpendingTargetPeriod];
    edit: [target: SpendingTarget];
    toggle: [target: SpendingTarget];
    delete: [target: SpendingTarget];
}>();
</script>

<template>
    <div>
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ t('settings.budgets.managementTitle') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ t('settings.budgets.managementDescription') }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ t('settings.budgets.statusNote') }}
                </p>
            </div>
            <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('settings.budgets.add') }}
            </Button>
        </div>

        <LoadingSkeleton v-if="loading" :rows="6" />

        <EmptyState
            v-else-if="!hasTargets"
            :title="t('settings.budgets.emptyTitle')"
            :description="t('settings.budgets.emptyDescription')"
        >
            <Button class="rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('settings.budgets.add') }}
            </Button>
        </EmptyState>

        <Tabs
            v-else
            :model-value="activePeriodTab"
            class="space-y-5"
            @update:model-value="
                emit('update:activePeriodTab', $event as SpendingTargetPeriod)
            "
        >
            <TabsList
                class="grid h-auto w-full grid-cols-3 rounded-3xl border border-border/60 bg-background/75 p-1"
            >
                <TabsTrigger
                    v-for="section in periodSections"
                    :key="section.period"
                    :value="section.period"
                    class="rounded-2xl px-4 py-3"
                >
                    <div class="flex flex-col items-center gap-1 text-center">
                        <span>{{ section.label }}</span>
                        <span class="text-[11px] text-muted-foreground">
                            {{ section.count }}
                        </span>
                    </div>
                </TabsTrigger>
            </TabsList>

            <TabsContent
                v-for="section in periodSections"
                :key="`${section.period}-content`"
                :value="section.period"
                class="mt-0"
            >
                <div
                    class="rounded-3xl border border-border/60 bg-background/70 p-5 sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 border-b border-border/60 pb-5 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ section.label }}
                            </p>
                            <h3
                                class="mt-1 text-xl font-semibold tracking-tight"
                            >
                                {{
                                    t(
                                        'settings.budgets.periodSectionDescription',
                                        {
                                            period: section.label.toLowerCase(),
                                        },
                                    )
                                }}
                            </h3>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{
                                    t('settings.budgets.filteredCount', {
                                        count: section.count,
                                    })
                                }}
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            class="rounded-2xl"
                            @click="emit('create', section.period)"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            {{
                                t('settings.budgets.addForPeriod', {
                                    period: section.label,
                                })
                            }}
                        </Button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <BudgetTargetCard
                            v-for="entry in section.entries"
                            :key="entry.target.id"
                            :entry="entry"
                            @edit="emit('edit', $event)"
                            @toggle="emit('toggle', $event)"
                            @delete="emit('delete', $event)"
                        />

                        <EmptyState
                            v-if="section.entries.length === 0"
                            :title="t('settings.budgets.emptyTitle')"
                            :description="
                                statusFilter === 'all'
                                    ? t(
                                          'settings.budgets.periodSectionDescription',
                                          {
                                              period: section.label.toLowerCase(),
                                          },
                                      )
                                    : t('settings.budgets.filterEmpty')
                            "
                        />
                    </div>
                </div>
            </TabsContent>
        </Tabs>
    </div>
</template>
