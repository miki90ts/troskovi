<script setup lang="ts">
import {
    AlertTriangle,
    Calendar,
    CheckCircle2,
    Edit2,
    Trash2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { t, formatCurrency, formatShortDate } from '@/lib/i18n';
import type { Debt } from '@/types/models';

const props = defineProps<{
    debt: Debt;
}>();

const emit = defineEmits<{
    edit: [debt: Debt];
    delete: [debt: Debt];
    settle: [debt: Debt];
}>();

function statusColor(status: string) {
    switch (status) {
        case 'active':
            return 'text-blue-600 bg-blue-500/10 border-blue-500/20';
        case 'settled':
            return 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20';
        case 'overdue':
            return 'text-red-600 bg-red-500/10 border-red-500/20';
        default:
            return 'text-muted-foreground bg-muted/10 border-border';
    }
}

function statusLabel(status: string) {
    switch (status) {
        case 'active':
            return t('debts.statusActive');
        case 'settled':
            return t('debts.statusSettled');
        case 'overdue':
            return t('debts.statusOverdue');
        default:
            return status;
    }
}

function progressBarColor(debt: Debt) {
    if (debt.status === 'settled') {
        return 'bg-emerald-500';
    }

    if (debt.progress_percent >= 80) {
        return 'bg-blue-500';
    }

    if (debt.progress_percent >= 50) {
        return 'bg-yellow-500';
    }

    return 'bg-orange-500';
}
</script>

<template>
    <div
        class="group relative flex flex-col gap-4 rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur transition hover:shadow-md"
    >
        <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3
                        class="truncate text-base font-semibold text-foreground"
                    >
                        {{ debt.person_name }}
                    </h3>
                    <span
                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase"
                        :class="statusColor(debt.status)"
                    >
                        {{ statusLabel(debt.status) }}
                    </span>
                </div>
                <p class="mt-1 truncate text-sm text-muted-foreground">
                    {{ debt.description }}
                </p>
            </div>
            <div
                class="flex gap-1 opacity-0 transition group-hover:opacity-100"
            >
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 rounded-xl p-0"
                    @click="emit('edit', debt)"
                >
                    <Edit2 class="h-3.5 w-3.5" />
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-8 w-8 rounded-xl p-0 text-destructive hover:text-destructive"
                    @click="emit('delete', debt)"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </Button>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">
                    {{ t('debts.paid') }}
                </span>
                <span class="font-medium">
                    {{
                        formatCurrency(
                            debt.paid_amount,
                            debt.currency?.iso_code ?? 'RSD',
                        )
                    }}
                    /
                    {{
                        formatCurrency(
                            debt.amount,
                            debt.currency?.iso_code ?? 'RSD',
                        )
                    }}
                </span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-muted/40">
                <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="progressBarColor(debt)"
                    :style="{
                        width: `${Math.min(debt.progress_percent, 100)}%`,
                    }"
                />
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-muted-foreground">
                    {{ debt.progress_percent }}%
                </span>
                <span
                    v-if="debt.remaining_amount > 0"
                    class="text-muted-foreground"
                >
                    {{ t('debts.remaining') }}:
                    {{
                        formatCurrency(
                            debt.remaining_amount,
                            debt.currency?.iso_code ?? 'RSD',
                        )
                    }}
                </span>
            </div>
        </div>

        <div
            class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
        >
            <div class="flex items-center gap-1">
                <Calendar class="h-3 w-3" />
                {{ formatShortDate(debt.date) }}
            </div>
            <div v-if="debt.due_date" class="flex items-center gap-1">
                <AlertTriangle
                    v-if="debt.is_overdue"
                    class="h-3 w-3 text-red-500"
                />
                <Calendar v-else class="h-3 w-3" />
                <span
                    :class="debt.is_overdue ? 'font-medium text-red-600' : ''"
                >
                    {{ t('debts.dueDateShort') }}:
                    {{ formatShortDate(debt.due_date) }}
                </span>
            </div>
            <div v-if="debt.linked_transactions_count > 0" class="text-xs">
                {{ debt.linked_transactions_count }}
                {{ t('debts.linkedTransactions') }}
            </div>
        </div>

        <div
            v-if="debt.status === 'active' || debt.status === 'overdue'"
            class="border-t border-border/40 pt-3"
        >
            <Button
                variant="outline"
                size="sm"
                class="w-full rounded-2xl"
                @click="emit('settle', debt)"
            >
                <CheckCircle2 class="mr-2 h-3.5 w-3.5" />
                {{ t('debts.markSettled') }}
            </Button>
        </div>
    </div>
</template>
