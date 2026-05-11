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
import { t } from '@/lib/i18n';
import type {
    BankAccountFormErrors,
    BankAccountFormValues,
} from '@/lib/validation/bankAccountValidation';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    open: boolean;
    editingAccount: BankAccount | null;
    formSubmitting: boolean;
    colorPresets: string[];
    form: BankAccountFormValues;
    errors: BankAccountFormErrors;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [value: BankAccountFormValues];
    submit: [];
    close: [];
    applyPresetColor: [color: string];
}>();

function updateForm(patch: Partial<BankAccountFormValues>) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

function fieldErrorClass(field: keyof BankAccountFormValues): string {
    return props.errors[field]
        ? 'border-destructive focus-visible:ring-destructive/20'
        : '';
}

const nameModel = computed({
    get: () => props.form.name,
    set: (value: string) => updateForm({ name: value }),
});

const bankNameModel = computed({
    get: () => props.form.bank_name,
    set: (value: string) => updateForm({ bank_name: value }),
});

const accountNumberModel = computed({
    get: () => props.form.account_number,
    set: (value: string) => updateForm({ account_number: value }),
});

const currencyModel = computed({
    get: () => props.form.currency,
    set: (value: string) => updateForm({ currency: value }),
});

const initialBalanceModel = computed({
    get: () => props.form.initial_balance,
    set: (value: string) => updateForm({ initial_balance: value }),
});

const colorModel = computed({
    get: () => props.form.color,
    set: (value: string) => updateForm({ color: value }),
});
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
                        {{ t('finance.bankAccounts.accountEditBadge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{
                            props.editingAccount
                                ? t('finance.bankAccounts.editTitle')
                                : t('finance.bankAccounts.newTitle')
                        }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{
                            props.editingAccount
                                ? t('finance.bankAccounts.editDescription')
                                : t('finance.bankAccounts.createDescription')
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <FormField
                    :label="t('finance.bankAccounts.accountName')"
                    field-id="ba_name"
                    :error="props.errors.name"
                >
                    <template #default>
                        <Input
                            id="ba_name"
                            v-model="nameModel"
                            :placeholder="
                                t('finance.bankAccounts.accountNamePlaceholder')
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('name'),
                            ]"
                        />
                    </template>
                </FormField>

                <FormField
                    :label="t('finance.bankAccounts.bankName')"
                    field-id="ba_bank_name"
                    :error="props.errors.bank_name"
                >
                    <template #default>
                        <Input
                            id="ba_bank_name"
                            v-model="bankNameModel"
                            :placeholder="
                                t('finance.bankAccounts.bankNamePlaceholder')
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('bank_name'),
                            ]"
                        />
                    </template>
                </FormField>

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        :label="t('finance.bankAccounts.accountNumber')"
                        field-id="ba_account_number"
                        :error="props.errors.account_number"
                    >
                        <template #default>
                            <Input
                                id="ba_account_number"
                                v-model="accountNumberModel"
                                :placeholder="
                                    t('finance.bankAccounts.optional')
                                "
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('account_number'),
                                ]"
                            />
                        </template>
                    </FormField>
                    <FormField
                        :label="t('finance.bankAccounts.currency')"
                        field-id="ba_currency"
                        :error="props.errors.currency"
                    >
                        <template #default>
                            <Input
                                id="ba_currency"
                                v-model="currencyModel"
                                placeholder="RSD"
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('currency'),
                                ]"
                            />
                        </template>
                    </FormField>
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                    <FormField
                        :label="t('finance.bankAccounts.initialBalance')"
                        field-id="ba_initial_balance"
                        :error="props.errors.initial_balance"
                    >
                        <template #default>
                            <Input
                                id="ba_initial_balance"
                                v-model="initialBalanceModel"
                                type="number"
                                step="0.01"
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('initial_balance'),
                                ]"
                            />
                        </template>
                    </FormField>
                    <FormField
                        :label="t('finance.bankAccounts.chooseColor')"
                        field-id="ba_color"
                        :error="props.errors.color"
                    >
                        <template #default>
                            <Input
                                id="ba_color"
                                v-model="colorModel"
                                type="color"
                                :class="[
                                    'h-14 w-full rounded-2xl border-border/60 bg-background p-2 md:w-24',
                                    fieldErrorClass('color'),
                                ]"
                            />
                        </template>
                    </FormField>
                </div>

                <FormField :label="t('finance.bankAccounts.quickChoices')">
                    <template #default>
                        <div
                            class="flex flex-wrap gap-2 rounded-3xl border border-dashed border-border/70 bg-muted/20 p-3"
                        >
                            <button
                                v-for="preset in props.colorPresets"
                                :key="preset"
                                type="button"
                                class="h-10 w-10 rounded-2xl border border-white/40 shadow-sm transition hover:scale-105"
                                :style="{ backgroundColor: preset }"
                                @click="emit('applyPresetColor', preset)"
                            />
                        </div>
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
                        :disabled="props.formSubmitting"
                    >
                        {{
                            props.formSubmitting
                                ? t('finance.bankAccounts.saving')
                                : props.editingAccount
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
