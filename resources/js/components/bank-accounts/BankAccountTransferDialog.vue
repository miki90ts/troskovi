<script setup lang="ts">
import { computed } from 'vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
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
import type {
    TransferFormErrors,
    TransferFormValues,
} from '@/lib/validation/transferValidation';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    open: boolean;
    submitting: boolean;
    activeAccounts: BankAccount[];
    transferPreview: {
        fromCurrency: string;
        toCurrency: string;
        sourceAmount: number;
        destinationAmount: number | null;
    } | null;
    form: TransferFormValues;
    errors: TransferFormErrors;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [value: TransferFormValues];
    submit: [];
    close: [];
}>();

function updateForm(patch: Partial<TransferFormValues>) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

function fieldErrorClass(field: keyof TransferFormValues): string {
    return props.errors[field]
        ? 'border-destructive focus-visible:ring-destructive/20'
        : '';
}

const fromAccountModel = computed({
    get: () => props.form.from_account_id,
    set: (value: string) => updateForm({ from_account_id: value }),
});

const toAccountModel = computed({
    get: () => props.form.to_account_id,
    set: (value: string) => updateForm({ to_account_id: value }),
});

const amountModel = computed({
    get: () => props.form.amount,
    set: (value: string) => updateForm({ amount: value }),
});

const descriptionModel = computed({
    get: () => props.form.description,
    set: (value: string) => updateForm({ description: value }),
});

const availableToAccounts = computed(() =>
    props.activeAccounts.filter(
        (a) => String(a.id) !== props.form.from_account_id,
    ),
);
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="(value) => emit('update:open', value)"
    >
        <DialogContent
            class="max-h-[90vh] overflow-y-auto rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-xl"
        >
            <DialogHeader>
                <div
                    class="relative overflow-hidden border-b border-border/60 bg-card px-6 py-5"
                >
                    <div
                        class="absolute -top-10 left-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"
                    />
                    <div
                        class="absolute right-4 bottom-0 h-24 w-24 rounded-full bg-emerald-300/10 blur-3xl"
                    />
                    <div
                        class="relative inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                    >
                        {{ t('finance.bankAccounts.transferBadge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{ t('finance.bankAccounts.transferTitle') }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{ t('finance.bankAccounts.transferDescription') }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <FormField
                    :label="t('finance.bankAccounts.fromAccount')"
                    :error="props.errors.from_account_id"
                >
                    <template #default>
                        <Select v-model="fromAccountModel">
                            <SelectTrigger
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('from_account_id'),
                                ]"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('finance.bankAccounts.selectAccount')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="a in activeAccounts"
                                    :key="a.id"
                                    :value="String(a.id)"
                                >
                                    {{ a.name }} ({{ a.currency }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </template>
                </FormField>

                <FormField
                    :label="t('finance.bankAccounts.toAccount')"
                    :error="props.errors.to_account_id"
                >
                    <template #default>
                        <Select v-model="toAccountModel">
                            <SelectTrigger
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('to_account_id'),
                                ]"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('finance.bankAccounts.selectAccount')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="a in availableToAccounts"
                                    :key="a.id"
                                    :value="String(a.id)"
                                >
                                    {{ a.name }} ({{ a.currency }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </template>
                </FormField>

                <FormField
                    :label="t('finance.bankAccounts.sourceAmount')"
                    field-id="transfer_amount"
                    :error="props.errors.amount"
                >
                    <template #default>
                        <Input
                            id="transfer_amount"
                            v-model="amountModel"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('amount'),
                            ]"
                        />
                    </template>
                </FormField>

                <div
                    v-if="props.transferPreview"
                    class="rounded-2xl border border-border/60 bg-muted/20 px-4 py-3"
                >
                    <p
                        class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.destinationAmount') }}
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        <template
                            v-if="
                                props.transferPreview.destinationAmount !== null
                            "
                        >
                            {{
                                props.transferPreview.destinationAmount.toLocaleString(
                                    'sr-RS',
                                    {
                                        style: 'currency',
                                        currency:
                                            props.transferPreview.toCurrency,
                                    },
                                )
                            }}
                        </template>
                        <template v-else>
                            {{
                                t('finance.bankAccounts.destinationUnavailable')
                            }}
                        </template>
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ t('finance.bankAccounts.destinationPreview') }}
                    </p>
                </div>

                <FormField
                    :label="t('finance.bankAccounts.transferDescriptionLabel')"
                    field-id="transfer_desc"
                    :error="props.errors.description"
                >
                    <template #default>
                        <Input
                            id="transfer_desc"
                            v-model="descriptionModel"
                            :placeholder="
                                t(
                                    'finance.bankAccounts.transferDescriptionPlaceholder',
                                )
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('description'),
                            ]"
                        />
                    </template>
                </FormField>

                <DialogFooter class="border-t border-border/60 pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="rounded-2xl"
                        @click="emit('close')"
                    >
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button
                        class="rounded-2xl px-5"
                        type="submit"
                        :disabled="props.submitting"
                    >
                        {{
                            props.submitting
                                ? t('finance.bankAccounts.transfering')
                                : t('common.actions.transfer')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
