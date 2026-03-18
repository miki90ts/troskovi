<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    CalendarClock,
    Pencil,
    Plus,
    Power,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import RecurringTransactionFormDialog from '@/components/RecurringTransactionFormDialog.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useRecurringTransactions } from '@/composables/useRecurringTransactions';
import { useToast } from '@/composables/useToast';
import type { BreadcrumbItem } from '@/types';
import type { Category, RecurringTransaction } from '@/types/models';

const props = defineProps<{
    recurringTransactions: { data: RecurringTransaction[] };
    categories: { data: Category[] };
    accounts: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Recurring Transactions', href: '/recurring-transactions' },
];

const { success, error: showError } = useToast();
const { deleteRecurring } = useRecurringTransactions();

const activeTab = ref<'expense' | 'income'>('expense');
const showForm = ref(false);
const editingRecurring = ref<RecurringTransaction | null>(null);
const deactivateTarget = ref<RecurringTransaction | null>(null);

const filteredRecurring = computed(() =>
    props.recurringTransactions.data.filter(
        (item) => item.type === activeTab.value,
    ),
);

function openCreate() {
    editingRecurring.value = null;
    showForm.value = true;
}

function openEdit(item: RecurringTransaction) {
    editingRecurring.value = item;
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    editingRecurring.value = null;
}

function onSaved() {
    closeForm();
    router.reload();
}

async function handleDeactivate() {
    if (!deactivateTarget.value) {
        return;
    }

    try {
        await deleteRecurring(deactivateTarget.value.id);
        success('Recurring transaction deactivated');
        deactivateTarget.value = null;
        router.reload();
    } catch {
        showError('Failed to deactivate recurring transaction');
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function frequencyLabel(frequency: RecurringTransaction['frequency']): string {
    return frequency.charAt(0).toUpperCase() + frequency.slice(1);
}
</script>

<template>
    <Head title="Recurring Transactions" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Recurring Transactions
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Create repeating income and expenses that get posted
                        automatically.
                    </p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Add Recurring
                </Button>
            </div>

            <Tabs v-model="activeTab">
                <TabsList>
                    <TabsTrigger value="expense">Expense</TabsTrigger>
                    <TabsTrigger value="income">Income</TabsTrigger>
                </TabsList>

                <TabsContent value="expense">
                    <div class="rounded-xl border bg-card shadow-sm">
                        <div v-if="filteredRecurring.length === 0">
                            <EmptyState
                                title="No recurring expenses"
                                description="Add subscriptions, rent, or other repeating costs here."
                            >
                                <Button @click="openCreate">
                                    <Plus class="mr-2 h-4 w-4" /> Add Recurring
                                    Expense
                                </Button>
                            </EmptyState>
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Frequency</TableHead>
                                    <TableHead>Next Due</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Account</TableHead>
                                    <TableHead class="text-right"
                                        >Amount</TableHead
                                    >
                                    <TableHead class="w-24" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in filteredRecurring"
                                    :key="item.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <ArrowDownCircle
                                                class="h-4 w-4 text-red-500"
                                            />
                                            <div>
                                                <p class="font-medium">
                                                    {{ item.description }}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {{
                                                        item.payment_method.replace(
                                                            '_',
                                                            ' ',
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        frequencyLabel(item.frequency)
                                    }}</TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <CalendarClock
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                            {{ formatDate(item.next_due_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        item.category?.name ?? '—'
                                    }}</TableCell>
                                    <TableCell>{{
                                        item.bank_account?.name ?? '—'
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <CurrencyDisplay
                                            :amount="-item.amount"
                                            colored
                                            class="font-semibold"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7"
                                                @click="openEdit(item)"
                                            >
                                                <Pencil class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-destructive"
                                                @click="deactivateTarget = item"
                                            >
                                                <Power class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="income">
                    <div class="rounded-xl border bg-card shadow-sm">
                        <div v-if="filteredRecurring.length === 0">
                            <EmptyState
                                title="No recurring income"
                                description="Add salary, retainers, or other repeating income here."
                            >
                                <Button @click="openCreate">
                                    <Plus class="mr-2 h-4 w-4" /> Add Recurring
                                    Income
                                </Button>
                            </EmptyState>
                        </div>
                        <Table v-else>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Frequency</TableHead>
                                    <TableHead>Next Due</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Account</TableHead>
                                    <TableHead class="text-right"
                                        >Amount</TableHead
                                    >
                                    <TableHead class="w-24" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in filteredRecurring"
                                    :key="item.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <ArrowUpCircle
                                                class="h-4 w-4 text-green-500"
                                            />
                                            <div>
                                                <p class="font-medium">
                                                    {{ item.description }}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Last processed:
                                                    {{
                                                        item.last_processed_date
                                                            ? formatDate(
                                                                  item.last_processed_date,
                                                              )
                                                            : 'Never'
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        frequencyLabel(item.frequency)
                                    }}</TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <CalendarClock
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                            {{ formatDate(item.next_due_date) }}
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        item.category?.name ?? '—'
                                    }}</TableCell>
                                    <TableCell>{{
                                        item.bank_account?.name ?? '—'
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        <CurrencyDisplay
                                            :amount="item.amount"
                                            colored
                                            class="font-semibold"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7"
                                                @click="openEdit(item)"
                                            >
                                                <Pencil class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-7 w-7 text-destructive"
                                                @click="deactivateTarget = item"
                                            >
                                                <Power class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>
            </Tabs>
        </div>

        <RecurringTransactionFormDialog
            :open="showForm"
            :recurring-transaction="editingRecurring"
            :categories="categories.data"
            :accounts="accounts"
            :default-type="activeTab"
            @close="showForm = false"
            @saved="onSaved"
        />

        <ConfirmDialog
            :open="!!deactivateTarget"
            title="Deactivate Recurring Transaction"
            description="This recurring rule will stop generating future transactions, but existing transactions will remain."
            confirm-text="Deactivate"
            destructive
            @confirm="handleDeactivate"
            @cancel="deactivateTarget = null"
        />
    </AppLayout>
</template>
