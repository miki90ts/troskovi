<script setup lang="ts">
import { ArrowDownCircle, ArrowUpCircle, CalendarClock } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import PaymentMethodBadge from '@/components/transactions/PaymentMethodBadge.vue';
import { useRecurringTransactions } from '@/composables/useRecurringTransactions';
import { useToast } from '@/composables/useToast';
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

import type { Category, RecurringTransaction } from '@/types/models';

const props = defineProps<{
    open: boolean;
    recurringTransaction?: RecurringTransaction | null;
    categories: Category[];
    accounts: { id: number; name: string }[];
    defaultType?: 'income' | 'expense';
}>();

const emit = defineEmits<{
    close: [];
    saved: [];
}>();

const { createRecurring, updateRecurring } = useRecurringTransactions();
const { success, error: showError } = useToast();

const NO_CATEGORY_VALUE = '__none_category__';
const NO_BANK_ACCOUNT_VALUE = '__none_bank_account__';

const form = ref({
    type: 'expense' as 'income' | 'expense',
    amount: '',
    description: '',
    frequency: 'monthly' as 'daily' | 'weekly' | 'monthly',
    next_due_date: new Date().toISOString().split('T')[0],
    category_id: '',
    bank_account_id: '',
    payment_method: 'cash' as 'cash' | 'bank_account',
});

const submitting = ref(false);

const categorySelectValue = computed({
    get: () => form.value.category_id || NO_CATEGORY_VALUE,
    set: (value: string) => {
        form.value.category_id = value === NO_CATEGORY_VALUE ? '' : value;
    },
});

const bankAccountSelectValue = computed({
    get: () => form.value.bank_account_id || NO_BANK_ACCOUNT_VALUE,
    set: (value: string) => {
        form.value.bank_account_id =
            value === NO_BANK_ACCOUNT_VALUE ? '' : value;
    },
});

const usesBankAccount = computed(() => {
    return form.value.payment_method === 'bank_account';
});

function previewFrequencyLabel() {
    return (
        {
            daily: t('common.recurringFrequencies.dailyLower'),
            weekly: t('common.recurringFrequencies.weeklyLower'),
            monthly: t('common.recurringFrequencies.monthlyLower'),
        }[form.value.frequency] ?? form.value.frequency
    );
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            if (props.recurringTransaction) {
                form.value = {
                    type: props.recurringTransaction.type,
                    amount: String(props.recurringTransaction.amount),
                    description: props.recurringTransaction.description,
                    frequency: props.recurringTransaction.frequency,
                    next_due_date: props.recurringTransaction.next_due_date,
                    category_id: props.recurringTransaction.category?.id
                        ? String(props.recurringTransaction.category.id)
                        : '',
                    bank_account_id: props.recurringTransaction.bank_account?.id
                        ? String(props.recurringTransaction.bank_account.id)
                        : '',
                    payment_method: props.recurringTransaction.payment_method,
                };
            } else {
                form.value = {
                    type: props.defaultType ?? 'expense',
                    amount: '',
                    description: '',
                    frequency: 'monthly',
                    next_due_date: new Date().toISOString().split('T')[0],
                    category_id: '',
                    bank_account_id: '',
                    payment_method: 'cash',
                };
            }
        }
    },
);

const filteredCategories = () =>
    props.categories.filter((category) => category.type === form.value.type);

const selectedCategory = computed(() => {
    return (
        filteredCategories().find(
            (category) => String(category.id) === form.value.category_id,
        ) ?? null
    );
});

async function onSubmit() {
    submitting.value = true;

    try {
        const payload = {
            type: form.value.type,
            amount: parseFloat(form.value.amount),
            description: form.value.description,
            frequency: form.value.frequency,
            next_due_date: form.value.next_due_date,
            category_id: form.value.category_id
                ? parseInt(form.value.category_id)
                : null,
            bank_account_id:
                usesBankAccount.value && form.value.bank_account_id
                    ? parseInt(form.value.bank_account_id)
                    : null,
            payment_method: form.value.payment_method,
        };

        if (props.recurringTransaction) {
            await updateRecurring(props.recurringTransaction.id, payload);
            success(t('components.recurringForm.updated'));
        } else {
            await createRecurring(payload);
            success(t('components.recurringForm.created'));
        }

        emit('saved');
    } catch {
        showError(t('components.recurringForm.saveError'));
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Dialog
        :open="open"
        @update:open="
            (value: boolean) => {
                if (!value) emit('close');
            }
        "
    >
        <DialogContent
            class="max-h-[90vh] overflow-y-auto rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-2xl"
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
                        {{ t('components.recurringForm.badge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{
                            recurringTransaction
                                ? t('components.recurringForm.editTitle')
                                : t('components.recurringForm.newTitle')
                        }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{ t('components.recurringForm.description') }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="onSubmit">
                <div class="grid gap-2">
                    <Label
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >{{ t('common.labels.type') }}</Label
                    >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <Button
                            type="button"
                            class="h-auto justify-start rounded-2xl border px-4 py-4"
                            :variant="
                                form.type === 'expense' ? 'default' : 'outline'
                            "
                            @click="form.type = 'expense'"
                        >
                            <ArrowDownCircle class="mr-3 h-5 w-5" />
                            <span class="flex flex-col items-start">
                                <span class="font-medium">{{
                                    t('components.transactionForm.expenseTitle')
                                }}</span>
                                <span class="text-xs opacity-80">{{
                                    t(
                                        'components.recurringForm.expenseSubtitle',
                                    )
                                }}</span>
                            </span>
                        </Button>
                        <Button
                            type="button"
                            class="h-auto justify-start rounded-2xl border px-4 py-4"
                            :variant="
                                form.type === 'income' ? 'default' : 'outline'
                            "
                            @click="form.type = 'income'"
                        >
                            <ArrowUpCircle class="mr-3 h-5 w-5" />
                            <span class="flex flex-col items-start">
                                <span class="font-medium">{{
                                    t('components.transactionForm.incomeTitle')
                                }}</span>
                                <span class="text-xs opacity-80">{{
                                    t('components.recurringForm.incomeSubtitle')
                                }}</span>
                            </span>
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
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
                            min="0"
                            step="0.01"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.frequency') }}</Label
                        >
                        <Select v-model="form.frequency">
                            <SelectTrigger
                                class="h-11 w-full rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="daily">{{
                                    t('common.recurringFrequencies.daily')
                                }}</SelectItem>
                                <SelectItem value="weekly">{{
                                    t('common.recurringFrequencies.weekly')
                                }}</SelectItem>
                                <SelectItem value="monthly">{{
                                    t('common.recurringFrequencies.monthly')
                                }}</SelectItem>
                            </SelectContent>
                        </Select>
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
                            t('components.recurringForm.descriptionPlaceholder')
                        "
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label
                            for="next_due_date"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.nextDate') }}</Label
                        >
                        <Input
                            id="next_due_date"
                            v-model="form.next_due_date"
                            type="date"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.category') }}</Label
                        >
                        <Select v-model="categorySelectValue">
                            <SelectTrigger
                                class="h-11 w-full rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'components.recurringForm.selectCategory',
                                        )
                                    "
                                >
                                    <CategoryBadge
                                        v-if="selectedCategory"
                                        :category="selectedCategory"
                                        compact
                                        class="max-w-full"
                                    />
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NO_CATEGORY_VALUE">{{
                                    t('common.states.noneFeminine')
                                }}</SelectItem>
                                <SelectItem
                                    v-for="category in filteredCategories()"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    <CategoryBadge
                                        :category="category"
                                        compact
                                        class="max-w-full"
                                    />
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.paymentMethod') }}</Label
                        >
                        <Select v-model="form.payment_method">
                            <SelectTrigger
                                class="h-11 w-full rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue>
                                    <PaymentMethodBadge
                                        :payment-method="form.payment_method"
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
                    </div>
                    <div v-if="usesBankAccount" class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.bankAccount') }}</Label
                        >
                        <Select v-model="bankAccountSelectValue">
                            <SelectTrigger
                                class="h-11 w-full rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t(
                                            'components.recurringForm.selectAccount',
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
                    </div>
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
                                {{ t('components.recurringForm.rulePreview') }}
                            </p>
                            <p
                                class="mt-1 text-xs leading-5 text-muted-foreground"
                            >
                                <CalendarClock
                                    class="mr-1 inline h-3.5 w-3.5"
                                />
                                {{
                                    t('components.recurringForm.ruleSentence', {
                                        frequency: previewFrequencyLabel(),
                                        date: form.next_due_date,
                                    })
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
                        class="rounded-2xl px-5"
                        type="submit"
                        :disabled="submitting"
                    >
                        {{
                            submitting
                                ? t('finance.bankAccounts.saving')
                                : recurringTransaction
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
