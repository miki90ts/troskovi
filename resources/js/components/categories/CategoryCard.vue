<script setup lang="ts">
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Pencil,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { t } from '@/lib/i18n';
import type { Category } from '@/types';

const props = defineProps<{
    category: Category;
}>();

const emit = defineEmits<{
    edit: [category: Category];
    delete: [category: Category];
}>();

const isExpense = computed(() => props.category.type === 'expense');
const badgeClass = computed(() =>
    isExpense.value
        ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300'
        : 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-300',
);
</script>

<template>
    <div
        class="group flex min-h-32 items-center justify-between rounded-3xl border border-border/60 bg-background/80 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
        <div class="flex items-center gap-3">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-semibold shadow-sm"
                :style="{
                    backgroundColor: (category.color ?? '#6b7280') + '20',
                    color: category.color ?? '#6b7280',
                }"
            >
                {{ category.icon ?? category.name.charAt(0) }}
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <p class="font-medium">
                        {{ category.name }}
                    </p>
                    <span
                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium"
                        :class="badgeClass"
                    >
                        <ArrowDownCircle v-if="isExpense" class="h-3 w-3" />
                        <ArrowUpCircle v-else class="h-3 w-3" />
                        {{
                            isExpense
                                ? t('finance.categories.expenseLabel')
                                : t('finance.categories.incomeLabel')
                        }}
                    </span>
                </div>
                <p
                    v-if="category.is_system"
                    class="text-xs text-muted-foreground"
                >
                    {{ t('finance.categories.systemCategory') }}
                </p>
                <p v-else class="text-xs text-muted-foreground">
                    {{ t('finance.categories.customCategory') }}
                </p>
            </div>
        </div>
        <div v-if="!category.is_system" class="flex gap-1">
            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-2xl"
                @click="emit('edit', category)"
            >
                <Pencil class="h-3.5 w-3.5" />
            </Button>
            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-2xl text-destructive"
                @click="emit('delete', category)"
            >
                <Trash2 class="h-3.5 w-3.5" />
            </Button>
        </div>
    </div>
</template>
