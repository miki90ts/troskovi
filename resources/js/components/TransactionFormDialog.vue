<script setup lang="ts">
import { Landmark, Wallet } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useTransactions } from '@/composables/useTransactions';
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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';

import type { Category, Transaction } from '@/types/models';

const props = defineProps<{
    open: boolean;
    transaction?: Transaction | null;
    categories: Category[];
    accounts: { id: number; name: string }[];
    defaultType?: 'income' | 'expense';
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const { createTransaction, updateTransaction } = useTransactions();
const { success, error: showError } = useToast();

const form = ref({
    type: 'expense' as 'income' | 'expense',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    description: '',
    category_id: '',
    bank_account_id: '',
    payment_method: 'cash' as 'cash' | 'bank_account',
    notes: '',
});

const submitting = ref(false);
const errors = ref<Record<string, string>>({});

const resolvedType = computed<'income' | 'expense'>(() => {
    return props.transaction?.type ?? props.defaultType ?? 'expense';
});

const resolvedTypeTitle = computed(() => {
    return resolvedType.value === 'expense'
        ? t('components.transactionForm.expenseTitle')
        : t('components.transactionForm.incomeTitle');
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
                    payment_method: props.transaction.payment_method,
                    notes: props.transaction.notes ?? '',
                };
            } else {
                form.value = {
                    type: resolvedType.value,
                    amount: '',
                    date: new Date().toISOString().split('T')[0],
                    description: '',
                    category_id: '',
                    bank_account_id: '',
                    payment_method: 'cash',
                    notes: '',
                };
            }
            errors.value = {};
        }
    },
);

const filteredCategories = () =>
    props.categories.filter((c) => c.type === form.value.type);

async function onSubmit() {
    submitting.value = true;
    errors.value = {};

    try {
        const payload: Record<string, unknown> = {
            type: form.value.type,
            amount: parseFloat(form.value.amount),
            date: form.value.date,
            description: form.value.description,
            payment_method:
                form.value.type === 'expense'
                    ? form.value.payment_method
                    : 'bank_account',
            notes: form.value.notes || null,
            category_id: form.value.category_id
                ? parseInt(form.value.category_id)
                : null,
            bank_account_id: form.value.bank_account_id
                ? parseInt(form.value.bank_account_id)
                : null,
        };

        if (props.transaction) {
            await updateTransaction(props.transaction.id, payload);
            success(t('components.transactionForm.updated'));
        } else {
            await createTransaction(payload);
            success(t('components.transactionForm.created'));
        }

        emit('saved');
    } catch (e: any) {
        if (e.response?.status === 422) {
            const validationErrors = e.response.data.errors;

            for (const key in validationErrors) {
                errors.value[key] = validationErrors[key][0];
            }
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
                    <div class="grid gap-2">
                        <Label
                            for="amount"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.amount') }}</Label
                        >
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                        <p
                            v-if="errors.amount"
                            class="text-xs text-destructive"
                        >
                            {{ errors.amount }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="date"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.date') }}</Label
                        >
                        <Input
                            id="date"
                            v-model="form.date"
                            type="date"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                        <p v-if="errors.date" class="text-xs text-destructive">
                            {{ errors.date }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label
                        for="description"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >{{ t('common.labels.description') }}</Label
                    >
                    <Input
                        id="description"
                        v-model="form.description"
                        :placeholder="
                            t(
                                'components.transactionForm.descriptionPlaceholder',
                            )
                        "
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                    <p
                        v-if="errors.description"
                        class="text-xs text-destructive"
                    >
                        {{ errors.description }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.category') }}</Label
                        >
                        <Select v-model="form.category_id">
                            <SelectTrigger
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'components.transactionForm.selectCategory',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="cat in filteredCategories()"
                                    :key="cat.id"
                                    :value="String(cat.id)"
                                >
                                    {{ cat.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="form.type === 'expense'" class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.paymentMethod') }}</Label
                        >
                        <Select v-model="form.payment_method">
                            <SelectTrigger
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">{{
                                    t('common.paymentMethods.cash')
                                }}</SelectItem>
                                <SelectItem value="bank_account">
                                    {{ t('common.paymentMethods.bankAccount') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-border/60 bg-muted/20 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <Landmark class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">
                                    {{
                                        t(
                                            'components.transactionForm.incomeAccountFlowTitle',
                                        )
                                    }}
                                </p>
                                <p
                                    class="mt-1 text-xs leading-5 text-muted-foreground"
                                >
                                    {{
                                        t(
                                            'components.transactionForm.incomeAccountFlowDescription',
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        form.payment_method === 'bank_account' ||
                        form.type === 'income'
                    "
                    class="grid gap-2"
                >
                    <Label
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >{{ t('common.labels.bankAccount') }}</Label
                    >
                    <Select v-model="form.bank_account_id">
                        <SelectTrigger
                            class="h-11 rounded-2xl border-border/60 bg-background"
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
                            <SelectItem value="">{{
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
                </div>

                <div class="grid gap-2">
                    <Label
                        for="notes"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >{{ t('common.labels.notes') }}</Label
                    >
                    <Input
                        id="notes"
                        v-model="form.notes"
                        :placeholder="
                            t('components.transactionForm.notesPlaceholder')
                        "
                        class="h-11 rounded-2xl border-border/60 bg-background"
                    />
                </div>

                <div
                    class="rounded-3xl border border-dashed border-border/70 bg-muted/20 p-4"
                >
                    <div class="flex items-start gap-3">
                        <div class="rounded-2xl bg-primary/10 p-2 text-primary">
                            <Wallet
                                v-if="
                                    form.payment_method === 'cash' &&
                                    form.type === 'expense'
                                "
                                class="h-4 w-4"
                            />
                            <Landmark v-else class="h-4 w-4" />
                        </div>
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
                                {{
                                    form.type === 'expense'
                                        ? form.payment_method === 'cash'
                                            ? t(
                                                  'components.transactionForm.bookingExpenseCash',
                                              )
                                            : t(
                                                  'components.transactionForm.bookingExpenseBank',
                                              )
                                        : t(
                                              'components.transactionForm.bookingIncome',
                                          )
                                }}
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
