<script setup lang="ts">
import { CircleDollarSign } from 'lucide-vue-next';
import { computed } from 'vue';
import FormField from '@/components/forms/FormField.vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type { BudgetFormErrors } from '@/lib/validation/spendingTargetValidation';
import type {
    BudgetFormState,
    Category,
    CurrencySummary,
    SpendingTarget,
    SpendingTargetPeriod,
} from '@/types';

const props = defineProps<{
    open: boolean;
    formSubmitting: boolean;
    editingTarget: SpendingTarget | null;
    categories: Category[];
    currencies: CurrencySummary[];
    periodOptions: SpendingTargetPeriod[];
    form: BudgetFormState;
    errors: BudgetFormErrors;
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

function fieldErrorClass(field: keyof BudgetFormState): string {
    return props.errors[field]
        ? 'border-destructive focus-visible:ring-destructive/20'
        : '';
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
                <FormField
                    :label="t('settings.budgets.period')"
                    field-id="budget-period"
                    :error="props.errors.period"
                    label-class="text-sm font-medium"
                >
                    <template #default>
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
                                :class="[
                                    'h-11 rounded-2xl',
                                    fieldErrorClass('period'),
                                ]"
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
                    </template>
                </FormField>

                <FormField
                    :label="t('settings.budgets.scope')"
                    field-id="budget-category"
                    :error="props.errors.categoryValue"
                    label-class="text-sm font-medium"
                >
                    <template #default>
                        <Select
                            :model-value="form.categoryValue"
                            @update:model-value="
                                updateForm({ categoryValue: String($event) })
                            "
                        >
                            <SelectTrigger
                                id="budget-category"
                                :class="[
                                    'h-11 rounded-2xl',
                                    fieldErrorClass('categoryValue'),
                                ]"
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
                    </template>
                    <template #hint>
                        <p class="text-sm text-muted-foreground">
                            {{ t('settings.budgets.scopeHint') }}
                        </p>
                    </template>
                </FormField>

                <FormField
                    :label="t('settings.budgets.targetAmount')"
                    field-id="budget-amount"
                    :error="props.errors.targetAmount"
                    label-class="text-sm font-medium"
                >
                    <template #default>
                        <Input
                            id="budget-amount"
                            :model-value="form.targetAmount"
                            type="number"
                            min="0"
                            step="0.01"
                            :class="[
                                'h-11 rounded-2xl',
                                fieldErrorClass('targetAmount'),
                            ]"
                            @update:model-value="
                                updateForm({ targetAmount: String($event) })
                            "
                        />
                    </template>
                </FormField>

                <FormField
                    :label="t('common.labels.currency')"
                    field-id="budget-currency"
                    :error="props.errors.currency_id"
                    label-class="text-sm font-medium"
                >
                    <template #default>
                        <Select
                            :model-value="form.currency_id"
                            @update:model-value="
                                updateForm({ currency_id: String($event) })
                            "
                        >
                            <SelectTrigger
                                id="budget-currency"
                                :class="[
                                    'h-11 rounded-2xl',
                                    fieldErrorClass('currency_id'),
                                ]"
                            >
                                <SelectValue
                                    :placeholder="t('common.labels.currency')"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="currency in props.currencies"
                                    :key="currency.id"
                                    :value="String(currency.id)"
                                >
                                    {{ currency.iso_code }} -
                                    {{ currency.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </template>
                </FormField>

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
