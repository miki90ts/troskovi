<script setup lang="ts">
import { ref, watch } from 'vue';
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
                    type: props.defaultType ?? 'expense',
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
            success('Transaction updated');
        } else {
            await createTransaction(payload);
            success('Transaction created');
        }

        emit('saved');
    } catch (e: any) {
        if (e.response?.status === 422) {
            const validationErrors = e.response.data.errors;

            for (const key in validationErrors) {
                errors.value[key] = validationErrors[key][0];
            }
        } else {
            showError('Failed to save transaction');
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
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>
                    {{ transaction ? 'Edit' : 'New' }} Transaction
                </DialogTitle>
                <DialogDescription>
                    {{
                        transaction
                            ? 'Update the transaction details.'
                            : 'Add a new income or expense.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="onSubmit">
                <!-- Type -->
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

                <!-- Amount + Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="amount">Amount</Label>
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
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
                        <Label for="date">Date</Label>
                        <Input
                            id="date"
                            v-model="form.date"
                            type="date"
                            required
                        />
                        <p v-if="errors.date" class="text-xs text-destructive">
                            {{ errors.date }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input
                        id="description"
                        v-model="form.description"
                        placeholder="What was this for?"
                        required
                    />
                    <p
                        v-if="errors.description"
                        class="text-xs text-destructive"
                    >
                        {{ errors.description }}
                    </p>
                </div>

                <!-- Category -->
                <div class="grid gap-2">
                    <Label>Category</Label>
                    <Select v-model="form.category_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Select category" />
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

                <!-- Payment Method (expenses only) -->
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

                <!-- Bank Account -->
                <div
                    v-if="
                        form.payment_method === 'bank_account' ||
                        form.type === 'income'
                    "
                    class="grid gap-2"
                >
                    <Label>Bank Account</Label>
                    <Select v-model="form.bank_account_id">
                        <SelectTrigger>
                            <SelectValue
                                placeholder="Select account (optional)"
                            />
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

                <!-- Notes -->
                <div class="grid gap-2">
                    <Label for="notes">Notes (optional)</Label>
                    <Input
                        id="notes"
                        v-model="form.notes"
                        placeholder="Additional notes..."
                    />
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
                                : transaction
                                  ? 'Update'
                                  : 'Create'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
