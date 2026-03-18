<script setup lang="ts">
import { ref, watch } from 'vue';
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
            bank_account_id: form.value.bank_account_id
                ? parseInt(form.value.bank_account_id)
                : null,
            payment_method:
                form.value.type === 'expense'
                    ? form.value.payment_method
                    : 'bank_account',
        };

        if (props.recurringTransaction) {
            await updateRecurring(props.recurringTransaction.id, payload);
            success('Recurring transaction updated');
        } else {
            await createRecurring(payload);
            success('Recurring transaction created');
        }

        emit('saved');
    } catch {
        showError('Failed to save recurring transaction');
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
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ recurringTransaction ? 'Edit' : 'New' }} Recurring
                    Transaction
                </DialogTitle>
                <DialogDescription>
                    Configure a repeating income or expense template.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="onSubmit">
                <div class="grid gap-2">
                    <Label>Type</Label>
                    <div class="flex gap-2">
                        <Button
                            type="button"
                            size="sm"
                            :variant="
                                form.type === 'expense' ? 'default' : 'outline'
                            "
                            @click="form.type = 'expense'"
                        >
                            Expense
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            :variant="
                                form.type === 'income' ? 'default' : 'outline'
                            "
                            @click="form.type = 'income'"
                        >
                            Income
                        </Button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="amount">Amount</Label>
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Frequency</Label>
                        <Select v-model="form.frequency">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="daily">Daily</SelectItem>
                                <SelectItem value="weekly">Weekly</SelectItem>
                                <SelectItem value="monthly">Monthly</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        v-model="form.description"
                        placeholder="e.g. Netflix, Salary"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="next_due_date">Next Due Date</Label>
                        <Input
                            id="next_due_date"
                            v-model="form.next_due_date"
                            type="date"
                            required
                        />
                    </div>
                    <div v-if="form.type === 'expense'" class="grid gap-2">
                        <Label>Payment Method</Label>
                        <Select v-model="form.payment_method">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="cash">Cash</SelectItem>
                                <SelectItem value="bank_account"
                                    >Bank Account</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Category</Label>
                        <Select v-model="form.category_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">None</SelectItem>
                                <SelectItem
                                    v-for="category in filteredCategories()"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Bank Account</Label>
                        <Select v-model="form.bank_account_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">None</SelectItem>
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

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('close')"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="submitting">
                        {{
                            submitting
                                ? 'Saving...'
                                : recurringTransaction
                                  ? 'Update'
                                  : 'Create'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
