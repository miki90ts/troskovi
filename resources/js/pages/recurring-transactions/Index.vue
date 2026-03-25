<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    CalendarClock,
    Clock3,
    Pencil,
    Plus,
    Power,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import { formatCurrency, t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Category, RecurringTransaction } from '@/types/models';

const props = defineProps<{
    recurringTransactions: { data: RecurringTransaction[] };
    categories: { data: Category[] };
    accounts: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('finance.recurring.head'), href: '/recurring-transactions' },
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
const expenseRecurring = computed(() =>
    props.recurringTransactions.data.filter((item) => item.type === 'expense'),
);
const incomeRecurring = computed(() =>
    props.recurringTransactions.data.filter((item) => item.type === 'income'),
);
const activeTabLabel = computed(() =>
    activeTab.value === 'expense'
        ? t('finance.recurring.expenseLabel')
        : t('finance.recurring.incomeLabel'),
);
const activeTabDisplay = computed(() =>
    activeTab.value === 'expense'
        ? t('finance.recurring.expenseTitle')
        : t('finance.recurring.incomeTitle'),
);
const visibleAmountTotal = computed(() =>
    filteredRecurring.value.reduce((sum, item) => sum + item.amount, 0),
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
        success(t('finance.recurring.deactivated'));
        deactivateTarget.value = null;
        router.reload();
    } catch {
        showError(t('finance.recurring.deactivateError'));
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function frequencyLabel(frequency: RecurringTransaction['frequency']): string {
    return (
        {
            daily: 'Dnevno',
            weekly: t('common.recurringFrequencies.weekly'),
            monthly: t('common.recurringFrequencies.monthly'),
        }[frequency] ?? frequency
    );
}
</script>

<template>
    <Head :title="t('finance.recurring.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-6 shadow-sm"
            >
                <div
                    class="absolute -top-16 -left-12 h-48 w-48 rounded-full bg-primary/15 blur-3xl"
                />
                <div
                    class="absolute right-0 bottom-0 hidden h-56 w-56 rounded-full bg-emerald-300/10 blur-3xl lg:block"
                />
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-2xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.recurring.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.recurring.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('finance.recurring.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.recurring.expenseRules') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ expenseRecurring.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.recurring.incomeRules') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ incomeRecurring.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('common.labels.totalShown') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ formatCurrency(visibleAmountTotal) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-md">
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.recurring.activeTab')
                                    }}</span
                                >
                                <Clock3 class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold capitalize">
                                {{ activeTabDisplay }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.recurring.automationEntry')
                                    }}</span
                                >
                                <CalendarClock class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                {{
                                    t('finance.recurring.automationDescription')
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('finance.recurring.control') }}</span
                                >
                                <Power class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                {{ t('finance.recurring.controlDescription') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.recurring.managementTitle') }}
                        </p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight">
                            {{ t('finance.recurring.managementDescription') }}
                        </h2>
                    </div>
                    <Button class="h-11 rounded-2xl px-5" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('finance.recurring.add') }}
                    </Button>
                </div>

                <Tabs v-model="activeTab" class="mt-5 space-y-5">
                    <TabsList
                        class="grid h-auto w-full grid-cols-2 rounded-2xl border border-border/60 bg-background p-1 md:w-fit"
                    >
                        <TabsTrigger value="expense">{{
                            t('finance.recurring.expenseTitle')
                        }}</TabsTrigger>
                        <TabsTrigger value="income">{{
                            t('finance.recurring.incomeTitle')
                        }}</TabsTrigger>
                    </TabsList>

                    <TabsContent value="expense" class="mt-0">
                        <div
                            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ activeTabLabel }}
                                </p>
                                <h3
                                    class="mt-1 text-lg font-semibold tracking-tight"
                                >
                                    {{
                                        t(
                                            'finance.recurring.expenseSectionDescription',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div
                                class="flex items-center gap-3 text-sm text-muted-foreground"
                            >
                                <span>{{
                                    t('finance.categories.totalCount', {
                                        count: expenseRecurring.length,
                                    })
                                }}</span>
                                <span
                                    class="hidden h-1 w-1 rounded-full bg-border sm:block"
                                />
                                <span>{{
                                    t('finance.recurring.availableAccounts', {
                                        count: accounts.length,
                                    })
                                }}</span>
                            </div>
                        </div>
                        <div
                            class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
                        >
                            <div v-if="filteredRecurring.length === 0">
                                <EmptyState
                                    :title="
                                        t('finance.recurring.emptyExpenseTitle')
                                    "
                                    :description="
                                        t(
                                            'finance.recurring.emptyExpenseDescription',
                                        )
                                    "
                                >
                                    <Button
                                        class="rounded-2xl px-5"
                                        @click="openCreate"
                                    >
                                        <Plus class="mr-2 h-4 w-4" />
                                        {{
                                            t(
                                                'finance.recurring.emptyExpenseAction',
                                            )
                                        }}
                                    </Button>
                                </EmptyState>
                            </div>
                            <Table v-else>
                                <TableHeader class="bg-muted/30">
                                    <TableRow>
                                        <TableHead>{{
                                            t('common.labels.description')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.frequency')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.nextDate')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.category')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.account')
                                        }}</TableHead>
                                        <TableHead class="text-right">{{
                                            t('common.labels.amount')
                                        }}</TableHead>
                                        <TableHead class="w-24" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="item in filteredRecurring"
                                        :key="item.id"
                                        class="border-border/60"
                                    >
                                        <TableCell>
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-500/10"
                                                >
                                                    <ArrowDownCircle
                                                        class="h-4 w-4 text-red-500"
                                                    />
                                                </div>
                                                <div>
                                                    <p class="font-medium">
                                                        {{ item.description }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        {{
                                                            item.payment_method ===
                                                            'bank_account'
                                                                ? t(
                                                                      'common.paymentMethods.bankAccount',
                                                                  )
                                                                : t(
                                                                      'common.paymentMethods.cash',
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
                                                {{
                                                    formatDate(
                                                        item.next_due_date,
                                                    )
                                                }}
                                            </div>
                                        </TableCell>
                                        <TableCell>{{
                                            item.category?.name ??
                                            t('common.states.uncategorized')
                                        }}</TableCell>
                                        <TableCell>{{
                                            item.bank_account?.name ??
                                            t('common.states.cashWallet')
                                        }}</TableCell>
                                        <TableCell class="text-right">
                                            <CurrencyDisplay
                                                :amount="-item.amount"
                                                colored
                                                class="font-semibold"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-2xl"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-2xl text-destructive"
                                                    @click="
                                                        deactivateTarget = item
                                                    "
                                                >
                                                    <Power
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </TabsContent>

                    <TabsContent value="income" class="mt-0">
                        <div
                            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ activeTabLabel }}
                                </p>
                                <h3
                                    class="mt-1 text-lg font-semibold tracking-tight"
                                >
                                    {{
                                        t(
                                            'finance.recurring.incomeSectionDescription',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div
                                class="flex items-center gap-3 text-sm text-muted-foreground"
                            >
                                <span>{{
                                    t('finance.categories.totalCount', {
                                        count: incomeRecurring.length,
                                    })
                                }}</span>
                                <span
                                    class="hidden h-1 w-1 rounded-full bg-border sm:block"
                                />
                                <span>{{
                                    t('finance.recurring.availableAccounts', {
                                        count: accounts.length,
                                    })
                                }}</span>
                            </div>
                        </div>
                        <div
                            class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
                        >
                            <div v-if="filteredRecurring.length === 0">
                                <EmptyState
                                    :title="
                                        t('finance.recurring.emptyIncomeTitle')
                                    "
                                    :description="
                                        t(
                                            'finance.recurring.emptyIncomeDescription',
                                        )
                                    "
                                >
                                    <Button
                                        class="rounded-2xl px-5"
                                        @click="openCreate"
                                    >
                                        <Plus class="mr-2 h-4 w-4" />
                                        {{
                                            t(
                                                'finance.recurring.emptyIncomeAction',
                                            )
                                        }}
                                    </Button>
                                </EmptyState>
                            </div>
                            <Table v-else>
                                <TableHeader class="bg-muted/30">
                                    <TableRow>
                                        <TableHead>{{
                                            t('common.labels.description')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.frequency')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.nextDate')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.category')
                                        }}</TableHead>
                                        <TableHead>{{
                                            t('common.labels.account')
                                        }}</TableHead>
                                        <TableHead class="text-right">{{
                                            t('common.labels.amount')
                                        }}</TableHead>
                                        <TableHead class="w-24" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="item in filteredRecurring"
                                        :key="item.id"
                                        class="border-border/60"
                                    >
                                        <TableCell>
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-500/10"
                                                >
                                                    <ArrowUpCircle
                                                        class="h-4 w-4 text-green-500"
                                                    />
                                                </div>
                                                <div>
                                                    <p class="font-medium">
                                                        {{ item.description }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        {{
                                                            t(
                                                                'finance.recurring.lastProcessed',
                                                                {
                                                                    date: item.last_processed_date
                                                                        ? formatDate(
                                                                              item.last_processed_date,
                                                                          )
                                                                        : t(
                                                                              'common.states.never',
                                                                          ),
                                                                },
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
                                                {{
                                                    formatDate(
                                                        item.next_due_date,
                                                    )
                                                }}
                                            </div>
                                        </TableCell>
                                        <TableCell>{{
                                            item.category?.name ??
                                            t('common.states.uncategorized')
                                        }}</TableCell>
                                        <TableCell>{{
                                            item.bank_account?.name ??
                                            t('common.states.noLinkedAccount')
                                        }}</TableCell>
                                        <TableCell class="text-right">
                                            <CurrencyDisplay
                                                :amount="item.amount"
                                                colored
                                                class="font-semibold"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-2xl"
                                                    @click="openEdit(item)"
                                                >
                                                    <Pencil
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-2xl text-destructive"
                                                    @click="
                                                        deactivateTarget = item
                                                    "
                                                >
                                                    <Power
                                                        class="h-3.5 w-3.5"
                                                    />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </TabsContent>
                </Tabs>
            </section>
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
            :title="t('finance.recurring.deactivateTitle')"
            :description="t('finance.recurring.deactivateDescription')"
            :confirm-text="t('finance.recurring.deactivateConfirm')"
            destructive
            @confirm="handleDeactivate"
            @cancel="deactivateTarget = null"
        />
    </AppLayout>
</template>
