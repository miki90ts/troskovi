<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { t } from '@/lib/i18n';
import type { Category } from '@/types';
import CategoryCollectionPanel from './CategoryCollectionPanel.vue';

const props = defineProps<{
    activeTab: 'expense' | 'income';
    expenseCategories: Category[];
    incomeCategories: Category[];
}>();

const emit = defineEmits<{
    'update:activeTab': [value: 'expense' | 'income'];
    create: [];
    edit: [category: Category];
    delete: [category: Category];
}>();
</script>

<template>
    <section
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ t('finance.categories.managementTitle') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ t('finance.categories.managementDescription') }}
                </h2>
            </div>
            <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('finance.categories.add') }}
            </Button>
        </div>

        <Tabs
            :model-value="props.activeTab"
            class="mt-5 space-y-5"
            @update:model-value="
                (value) =>
                    emit('update:activeTab', value as 'expense' | 'income')
            "
        >
            <TabsList
                class="grid h-auto w-full grid-cols-2 rounded-2xl border border-border/60 bg-background p-1 md:w-fit"
            >
                <TabsTrigger value="expense" class="rounded-xl px-4 py-2.5">
                    {{
                        t('finance.categories.expenseTab', {
                            count: expenseCategories.length,
                        })
                    }}
                </TabsTrigger>
                <TabsTrigger value="income" class="rounded-xl px-4 py-2.5">
                    {{
                        t('finance.categories.incomeTab', {
                            count: incomeCategories.length,
                        })
                    }}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="expense" class="mt-0">
                <CategoryCollectionPanel
                    :categories="expenseCategories"
                    tab="expense"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @delete="emit('delete', $event)"
                />
            </TabsContent>

            <TabsContent value="income" class="mt-0">
                <CategoryCollectionPanel
                    :categories="incomeCategories"
                    tab="income"
                    @create="emit('create')"
                    @edit="emit('edit', $event)"
                    @delete="emit('delete', $event)"
                />
            </TabsContent>
        </Tabs>
    </section>
</template>
