<script setup lang="ts">
import { Download, Eye, ShieldCheck, Upload, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import CategorySingleSelect from '@/components/categories/CategorySingleSelect.vue';
import FormField from '@/components/forms/FormField.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { Button } from '@/components/ui/button';
import { useValidationErrors } from '@/composables/useValidationErrors';
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
import { useToast } from '@/composables/useToast';
import { useTransactions } from '@/composables/useTransactions';
import { t } from '@/lib/i18n';
import {
    transactionValidationMessages,
    validateTransactionForm,
    type TransactionFormValues,
} from '@/lib/validation/transactionValidation';

import type { Category, Debt, Transaction } from '@/types/models';

const props = defineProps<{
    open: boolean;
    transaction?: Transaction | null;
    categories: Category[];
    accounts: { id: number; name: string }[];
    debts?: Debt[];
    defaultType?: 'income' | 'expense';
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const { createTransaction, updateTransaction } = useTransactions();
const { success, error: showError } = useToast();

const NO_BANK_ACCOUNT_VALUE = '__none__';
const NO_DEBT_VALUE = '__none__';

const form = ref({
    type: 'expense' as 'income' | 'expense',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    description: '',
    category_id: '',
    bank_account_id: '',
    debt_id: '',
    payment_method: 'cash' as 'cash' | 'bank_account',
    notes: '',
    is_warranty: false,
});

const receiptFile = ref<File | null>(null);
const receiptPreview = ref<string | null>(null);
const submitting = ref(false);
const {
    errors,
    clearErrors,
    clearAllErrors,
    fieldErrorClass,
    setErrors,
    setServerErrors,
} = useValidationErrors<keyof TransactionFormValues>();

const resolvedType = computed<'income' | 'expense'>(() => {
    return props.transaction?.type ?? props.defaultType ?? 'expense';
});

const resolvedTypeTitle = computed(() => {
    return resolvedType.value === 'expense'
        ? t('components.transactionForm.expenseTitle')
        : t('components.transactionForm.incomeTitle');
});

const bankAccountSelectValue = computed({
    get: () => form.value.bank_account_id || NO_BANK_ACCOUNT_VALUE,
    set: (value: string) => {
        form.value.bank_account_id =
            value === NO_BANK_ACCOUNT_VALUE ? '' : value;
    },
});

const debtSelectValue = computed({
    get: () => form.value.debt_id || NO_DEBT_VALUE,
    set: (value: string) => {
        form.value.debt_id = value === NO_DEBT_VALUE ? '' : value;
    },
});

const availableDebts = computed(() => props.debts ?? []);

const usesBankAccount = computed(() => {
    return form.value.payment_method === 'bank_account';
});

const bookingPreviewDescription = computed(() => {
    if (form.value.type === 'expense') {
        return form.value.payment_method === 'cash'
            ? t('components.transactionForm.bookingExpenseCash')
            : t('components.transactionForm.bookingExpenseBank');
    }

    return form.value.payment_method === 'cash'
        ? t('components.transactionForm.bookingIncomeCash')
        : t('components.transactionForm.bookingIncomeBank');
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.transaction) {
                form.value = {
                    type: props.transaction.type,
                    amount: String(props.transaction.amount),
                    date: props.transaction.date,
                    description: props.transaction.description,
                    category_id: props.transaction.category?.id
                        ? String(props.transaction.category.id)
                        : '',
                    bank_account_id: props.transaction.bank_account?.id
                        ? String(props.transaction.bank_account.id)
                        : '',
                    debt_id: props.transaction.debt?.id
                        ? String(props.transaction.debt.id)
                        : '',
                    payment_method: props.transaction.payment_method,
                    notes: props.transaction.notes ?? '',
                    is_warranty: props.transaction.is_warranty ?? false,
                };
            } else {
                form.value = {
                    type: resolvedType.value,
                    amount: '',
                    date: new Date().toISOString().split('T')[0],
                    description: '',
                    category_id: '',
                    bank_account_id: '',
                    debt_id: '',
                    payment_method: 'bank_account',
                    notes: '',
                    is_warranty: false,
                };
            }

            receiptFile.value = null;
            receiptPreview.value = null;
            clearAllErrors();
        }
    },
);

const filteredCategories = () =>
    props.categories.filter((c) => c.type === form.value.type);

const warrantyExpiresDate = computed(() => {
    if (!form.value.is_warranty || !form.value.date) {
        return null;
    }

    const date = new Date(form.value.date);
    date.setFullYear(date.getFullYear() + 2);

    return date.toLocaleDateString('sr-RS', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

const MAX_FILE_SIZE = 1024 * 1024; // 1 MB

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (file && file.size > MAX_FILE_SIZE) {
        errors.value.receipt = transactionValidationMessages.receiptMax;
        receiptFile.value = null;
        receiptPreview.value = null;
        input.value = '';

        return;
    }

    if (file && !file.type.startsWith('image/')) {
        errors.value.receipt = transactionValidationMessages.receiptImage;
        receiptFile.value = null;
        receiptPreview.value = null;
        input.value = '';

        return;
    }

    delete errors.value.receipt;
    receiptFile.value = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            receiptPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        receiptPreview.value = null;
    }
}

function removeReceipt() {
    receiptFile.value = null;
    receiptPreview.value = null;
}

function handleWarrantyChange() {
    if (!form.value.is_warranty) {
        removeReceipt();
    }
}

function getReceiptPreviewUrl(receiptUrl: string): string {
    return `${receiptUrl}${receiptUrl.includes('?') ? '&' : '?'}preview=1`;
}

watch(
    () => form.value.type,
    () => clearErrors('type', 'payment_method', 'bank_account_id'),
);

watch(
    () => form.value.amount,
    () => clearErrors('amount'),
);
watch(
    () => form.value.date,
    () => clearErrors('date'),
);
watch(
    () => form.value.description,
    () => clearErrors('description'),
);
watch(
    () => form.value.category_id,
    () => clearErrors('category_id'),
);
watch(
    () => form.value.payment_method,
    () => clearErrors('payment_method', 'bank_account_id'),
);
watch(
    () => form.value.bank_account_id,
    () => clearErrors('bank_account_id'),
);
watch(
    () => form.value.notes,
    () => clearErrors('notes'),
);
watch(
    () => form.value.debt_id,
    () => clearErrors('debt_id'),
);
watch(
    () => form.value.is_warranty,
    () => clearErrors('is_warranty', 'receipt'),
);
watch(receiptFile, () => clearErrors('receipt'));

async function onSubmit() {
    submitting.value = true;
    clearAllErrors();

    try {
        const frontErrors = validateTransactionForm(
            {
                ...form.value,
                receipt: receiptFile.value,
            },
            {
                categoryIds: props.categories.map((category) => category.id),
                bankAccountIds: props.accounts.map((account) => account.id),
                debts: availableDebts.value,
            },
        );

        if (Object.keys(frontErrors).length > 0) {
            setErrors(frontErrors);

            return;
        }

        const payload: Record<string, unknown> = {
            type: form.value.type,
            amount: parseFloat(form.value.amount),
            date: form.value.date,
            description: form.value.description,
            payment_method: form.value.payment_method,
            notes: form.value.notes || null,
            category_id: form.value.category_id
                ? parseInt(form.value.category_id)
                : null,
            bank_account_id:
                usesBankAccount.value && form.value.bank_account_id
                    ? parseInt(form.value.bank_account_id)
                    : null,
            debt_id: form.value.debt_id ? parseInt(form.value.debt_id) : null,
            is_warranty:
                form.value.type === 'expense' ? form.value.is_warranty : false,
        };

        let submitPayload: FormData | Record<string, unknown> = payload;

        if (receiptFile.value) {
            const formData = new FormData();

            for (const [key, value] of Object.entries(payload)) {
                if (value !== null && value !== undefined) {
                    formData.append(
                        key,
                        value === true
                            ? '1'
                            : value === false
                              ? '0'
                              : String(value),
                    );
                }
            }

            formData.append('receipt', receiptFile.value);
            submitPayload = formData;
        }

        if (props.transaction) {
            await updateTransaction(props.transaction.id, submitPayload);
            success(t('components.transactionForm.updated'));
        } else {
            await createTransaction(submitPayload);
            success(t('components.transactionForm.created'));
        }

        emit('saved');
    } catch (e: any) {
        if (e.response?.status === 422) {
            setServerErrors(e.response?.data?.errors);
        } else {
            showError(t('components.transactionForm.saveError'));
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (v: boolean) => {
                if (!v) emit('close');
            }
        "
    >
        <DialogContent
            class="max-h-[90vh] overflow-y-auto rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-2xl"
        >
            <DialogHeader>
                <div
                    class="border-b border-border/60 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.15),transparent_42%),linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,253,245,0.9))] px-6 py-5 dark:bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.22),transparent_38%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(13,148,136,0.14))]"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                    >
                        {{ t('components.transactionForm.badge') }}
                    </div>
                    <DialogTitle class="mt-4 text-2xl tracking-tight">
                        {{
                            transaction
                                ? `${t('common.actions.edit')} ${resolvedTypeTitle.toLowerCase()}`
                                : `${t('common.actions.add')} ${resolvedTypeTitle.toLowerCase()}`
                        }}
                    </DialogTitle>
                    <DialogDescription class="mt-2 max-w-xl text-sm leading-6">
                        {{
                            transaction
                                ? t(
                                      'components.transactionForm.editDescription',
                                  )
                                : t(
                                      'components.transactionForm.createDescription',
                                  )
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="onSubmit">
                <div class="grid gap-4 md:grid-cols-2">
                    <FormField
                        :label="t('common.labels.amount')"
                        field-id="amount"
                        :error="errors.amount"
                    >
                        <template #default>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('amount'),
                                ]"
                            />
                        </template>
                    </FormField>
                    <FormField
                        :label="t('common.labels.date')"
                        field-id="date"
                        :error="errors.date"
                    >
                        <template #default>
                            <Input
                                id="date"
                                v-model="form.date"
                                type="date"
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('date'),
                                ]"
                            />
                        </template>
                    </FormField>
                </div>

                <FormField
                    :label="t('common.labels.description')"
                    field-id="description"
                    :error="errors.description"
                >
                    <template #default>
                        <Input
                            id="description"
                            v-model="form.description"
                            :placeholder="
                                t(
                                    'components.transactionForm.descriptionPlaceholder',
                                )
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('description'),
                            ]"
                        />
                    </template>
                </FormField>

                <div class="grid gap-4 md:grid-cols-2">
                    <FormField
                        :label="t('common.labels.category')"
                        :error="errors.category_id"
                    >
                        <template #default>
                            <CategorySingleSelect
                                v-model="form.category_id"
                                :categories="filteredCategories()"
                                :placeholder="
                                    t(
                                        'components.transactionForm.selectCategory',
                                    )
                                "
                                :search-placeholder="
                                    t(
                                        'components.transactionForm.searchCategoryPlaceholder',
                                    )
                                "
                                :empty-results-label="
                                    t(
                                        'components.transactionForm.noCategoryResults',
                                    )
                                "
                                :clear-label="t('common.states.noneFeminine')"
                                :trigger-class="fieldErrorClass('category_id')"
                            />
                        </template>
                    </FormField>

                    <FormField
                        :label="t('common.labels.paymentMethod')"
                        :error="errors.payment_method"
                    >
                        <template #default>
                            <Select v-model="form.payment_method">
                                <SelectTrigger
                                    :class="[
                                        'h-11 w-full rounded-2xl border-border/60 bg-background',
                                        fieldErrorClass('payment_method'),
                                    ]"
                                >
                                    <SelectValue>
                                        <PaymentMethodBadge
                                            :payment-method="
                                                form.payment_method
                                            "
                                            compact
                                            class="max-w-full"
                                        />
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="cash">
                                        <PaymentMethodBadge
                                            payment-method="cash"
                                            compact
                                        />
                                    </SelectItem>
                                    <SelectItem value="bank_account">
                                        <PaymentMethodBadge
                                            payment-method="bank_account"
                                            compact
                                        />
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </template>
                    </FormField>
                </div>

                <FormField
                    v-if="usesBankAccount"
                    :label="t('common.labels.bankAccount')"
                    :error="errors.bank_account_id"
                >
                    <template #default>
                        <Select v-model="bankAccountSelectValue">
                            <SelectTrigger
                                :class="[
                                    'h-11 w-full rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('bank_account_id'),
                                ]"
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'components.transactionForm.selectAccountOptional',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NO_BANK_ACCOUNT_VALUE">{{
                                    t('common.states.none')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="account in accounts"
                                    :key="account.id"
                                    :value="String(account.id)"
                                >
                                    {{ account.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </template>
                </FormField>

                <FormField
                    :label="t('common.labels.notes')"
                    field-id="notes"
                    :error="errors.notes"
                >
                    <template #default>
                        <Input
                            id="notes"
                            v-model="form.notes"
                            :placeholder="
                                t('components.transactionForm.notesPlaceholder')
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('notes'),
                            ]"
                        />
                    </template>
                </FormField>

                <FormField
                    v-if="availableDebts.length > 0"
                    :label="t('components.transactionForm.linkedDebt')"
                    :error="errors.debt_id"
                >
                    <template #default>
                        <Select v-model="debtSelectValue">
                            <SelectTrigger
                                :class="[
                                    'h-11 w-full rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('debt_id'),
                                ]"
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'components.transactionForm.selectDebtOptional',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NO_DEBT_VALUE">{{
                                    t('common.states.none')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="debt in availableDebts"
                                    :key="debt.id"
                                    :value="String(debt.id)"
                                >
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="inline-flex shrink-0 rounded-md px-1.5 py-0.5 text-[10px] leading-none font-semibold uppercase"
                                            :class="
                                                debt.type === 'i_owe'
                                                    ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400'
                                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                                            "
                                        >
                                            {{
                                                debt.type === 'i_owe'
                                                    ? t('debts.iOweLabel')
                                                    : t('debts.owedToMeLabel')
                                            }}
                                        </span>
                                        <span>{{ debt.person_name }}</span>
                                        <span class="text-muted-foreground">
                                            {{
                                                debt.remaining_amount.toLocaleString(
                                                    'sr-RS',
                                                )
                                            }}
                                            RSD
                                        </span>
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </template>
                </FormField>

                <!-- Warranty section (expense only) -->
                <div
                    v-if="form.type === 'expense'"
                    class="space-y-4 rounded-3xl border border-border/60 bg-muted/20 p-4"
                >
                    <FormField :error="errors.is_warranty">
                        <template #default>
                            <Label
                                for="is_warranty"
                                class="flex cursor-pointer items-center gap-3"
                            >
                                <input
                                    id="is_warranty"
                                    v-model="form.is_warranty"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                                    @change="handleWarrantyChange"
                                />
                                <span
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    <ShieldCheck class="h-4 w-4 text-primary" />
                                    {{
                                        t(
                                            'components.transactionForm.warrantyCheckbox',
                                        )
                                    }}
                                </span>
                            </Label>
                        </template>
                    </FormField>

                    <template v-if="form.is_warranty">
                        <FormField
                            :label="
                                t('components.transactionForm.warrantyReceipt')
                            "
                            :error="errors.receipt"
                        >
                            <template #default>
                                <div
                                    v-if="
                                        !receiptPreview &&
                                        !props.transaction?.receipt_url
                                    "
                                    class="relative"
                                >
                                    <label
                                        class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-border/60 bg-background p-6 transition-colors hover:border-primary/40 hover:bg-primary/5"
                                    >
                                        <Upload
                                            class="h-6 w-6 text-muted-foreground"
                                        />
                                        <span
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'components.transactionForm.warrantyReceiptHint',
                                                )
                                            }}
                                        </span>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="sr-only"
                                            @change="onFileChange"
                                        />
                                    </label>
                                </div>
                                <div v-else class="relative">
                                    <img
                                        v-if="receiptPreview"
                                        :src="receiptPreview"
                                        alt="Receipt preview"
                                        class="max-h-48 rounded-2xl border border-border/60 object-contain"
                                    />
                                    <div
                                        v-else-if="
                                            props.transaction?.receipt_url
                                        "
                                        class="space-y-3 rounded-2xl border border-border/60 bg-background p-3"
                                    >
                                        <a
                                            :href="
                                                getReceiptPreviewUrl(
                                                    props.transaction
                                                        .receipt_url,
                                                )
                                            "
                                            target="_blank"
                                            class="block overflow-hidden rounded-2xl border border-border/60 bg-muted/20"
                                        >
                                            <img
                                                :src="
                                                    getReceiptPreviewUrl(
                                                        props.transaction
                                                            .receipt_url,
                                                    )
                                                "
                                                alt="Existing receipt preview"
                                                class="max-h-48 w-full object-contain"
                                            />
                                        </a>
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                :href="
                                                    getReceiptPreviewUrl(
                                                        props.transaction
                                                            .receipt_url,
                                                    )
                                                "
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-2xl border border-border/60 px-3 py-2 text-sm transition-colors hover:bg-muted"
                                            >
                                                <Eye class="h-4 w-4" />
                                                {{
                                                    t(
                                                        'finance.warranties.viewReceipt',
                                                    )
                                                }}
                                            </a>
                                            <a
                                                :href="
                                                    props.transaction
                                                        .receipt_url
                                                "
                                                target="_blank"
                                                class="inline-flex items-center gap-2 rounded-2xl border border-border/60 px-3 py-2 text-sm transition-colors hover:bg-muted"
                                            >
                                                <Download class="h-4 w-4" />
                                                {{
                                                    t(
                                                        'finance.warranties.downloadReceipt',
                                                    )
                                                }}
                                            </a>
                                            <label
                                                class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-border/60 px-3 py-2 text-sm transition-colors hover:bg-muted"
                                            >
                                                <Upload class="h-4 w-4" />
                                                {{
                                                    t(
                                                        'components.transactionForm.replaceReceipt',
                                                    )
                                                }}
                                                <input
                                                    type="file"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    class="sr-only"
                                                    @change="onFileChange"
                                                />
                                            </label>
                                        </div>
                                    </div>
                                    <Button
                                        v-if="receiptPreview"
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        class="absolute -top-2 -right-2 h-7 w-7 rounded-full bg-destructive/10 text-destructive hover:bg-destructive/20"
                                        @click="removeReceipt"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </template>
                        </FormField>

                        <div
                            v-if="warrantyExpiresDate"
                            class="flex items-center gap-2 rounded-2xl border border-primary/20 bg-primary/5 px-4 py-3"
                        >
                            <ShieldCheck class="h-4 w-4 text-primary" />
                            <span class="text-sm font-medium text-primary">
                                {{
                                    t(
                                        'components.transactionForm.warrantyExpires',
                                    )
                                }}: {{ warrantyExpiresDate }}
                            </span>
                        </div>
                    </template>
                </div>

                <div
                    class="rounded-3xl border border-dashed border-border/70 bg-muted/20 p-4"
                >
                    <div class="flex items-start gap-3">
                        <PaymentMethodBadge
                            :payment-method="form.payment_method"
                            compact
                            class="mt-0.5"
                        />
                        <div>
                            <p class="text-sm font-medium">
                                {{
                                    t(
                                        'components.transactionForm.bookingPreview',
                                    )
                                }}
                            </p>
                            <p
                                class="mt-1 text-xs leading-5 text-muted-foreground"
                            >
                                {{ bookingPreviewDescription }}
                            </p>
                        </div>
                    </div>
                </div>

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
                        type="submit"
                        class="rounded-2xl px-5"
                        :disabled="submitting"
                    >
                        {{
                            submitting
                                ? t('finance.bankAccounts.saving')
                                : transaction
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
