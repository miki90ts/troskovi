<script setup lang="ts">
import { CircleDollarSign } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type {
    BudgetFormState,
    Category,
    SpendingTarget,
    SpendingTargetPeriod,
} from '@/types';

const props = defineProps<{
    open: boolean;
    formSubmitting: boolean;
    editingTarget: SpendingTarget | null;
    categories: Category[];
    periodOptions: SpendingTargetPeriod[];
    form: BudgetFormState;
    overallSentinel: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [value: BudgetFormState];
    submit: [];
}>();

const title = computed(() =>
    props.editingTarget
        ? t('settings.budgets.editTitle')
        : t('settings.budgets.newTitle'),
);

const description = computed(() =>
    props.editingTarget
        ? t('settings.budgets.editDescription')
        : t('settings.budgets.createDescription'),
);

function labelForPeriod(period: SpendingTargetPeriod) {
    return t(`common.recurringFrequencies.${period}`);
}

function updateForm(patch: Partial<BudgetFormState>) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle>
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-5 py-2">
                <div class="grid gap-2">
                    <Label for="budget-period">{{
                        t('settings.budgets.period')
                    }}</Label>
                    <Select
                        :model-value="form.period"
                        @update:model-value="
                            updateForm({
                                period: $event as SpendingTargetPeriod,
                            })
                        "
                    >
                        <SelectTrigger
                            id="budget-period"
                            class="h-11 rounded-2xl"
                        >
                            <SelectValue
                                :placeholder="t('settings.budgets.period')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="period in periodOptions"
                                :key="period"
                                :value="period"
                            >
                                {{ labelForPeriod(period) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label for="budget-category">{{
                        t('settings.budgets.scope')
                    }}</Label>
                    <Select
                        :model-value="form.categoryValue"
                        @update:model-value="
                            updateForm({ categoryValue: String($event) })
                        "
                    >
                        <SelectTrigger
                            id="budget-category"
                            class="h-11 rounded-2xl"
                        >
                            <SelectValue
                                :placeholder="t('settings.budgets.scope')"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="overallSentinel">
                                {{ t('settings.budgets.overallOption') }}
                            </SelectItem>
                            <SelectItem
                                v-for="category in categories"
                                :key="category.id"
                                :value="String(category.id)"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-sm text-muted-foreground">
                        {{ t('settings.budgets.scopeHint') }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="budget-amount">{{
                        t('settings.budgets.targetAmount')
                    }}</Label>
                    <Input
                        id="budget-amount"
                        :model-value="form.targetAmount"
                        type="number"
                        min="0"
                        step="0.01"
                        class="h-11 rounded-2xl"
                        @update:model-value="
                            updateForm({ targetAmount: String($event) })
                        "
                    />
                </div>

                <Label
                    class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-4 py-3"
                >
                    <Checkbox
                        :checked="form.isActive"
                        @update:checked="
                            updateForm({ isActive: $event === true })
                        "
                    />
                    <span class="text-sm font-medium">{{
                        t('settings.budgets.activeOnCreate')
                    }}</span>
                </Label>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    class="rounded-2xl"
                    @click="emit('update:open', false)"
                >
                    {{ t('common.actions.cancel') }}
                </Button>
                <Button
                    class="rounded-2xl"
                    :disabled="formSubmitting"
                    @click="emit('submit')"
                >
                    <CircleDollarSign class="mr-2 h-4 w-4" />
                    {{
                        editingTarget
                            ? t('common.actions.update')
                            : t('common.actions.create')
                    }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
