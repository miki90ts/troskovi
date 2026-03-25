<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    Landmark,
    Wallet,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import KpiCard from '@/components/KpiCard.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useTransactions } from '@/composables/useTransactions';
import { formatShortDate, t } from '@/lib/i18n';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import type { BankAccountOverview, Transaction } from '@/types/models';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    account: { data: BankAccountOverview };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.bankAccounts'), href: '/bank-accounts' },
    {
        title: props.account.data.name,
        href: `/bank-accounts/${props.account.data.id}`,
    },
];

const { fetchTransactions } = useTransactions();
const transactions = ref<Transaction[]>([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await fetchTransactions({
            bank_account_id: props.account.data.id,
            per_page: 20,
        });
        transactions.value = res.data;
    } finally {
        loading.value = false;
    }
});

function formatDate(dateStr: string): string {
    return formatShortDate(dateStr);
}

function formatCurrency(amount: number): string {
    return amount.toLocaleString('sr-RS', {
        style: 'currency',
        currency: props.account.data.currency,
    });
}
</script>

<template>
    <Head :title="account.data.name" />
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
                        <div class="flex items-center gap-4">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="rounded-2xl"
                                as-child
                            >
                                <Link href="/bank-accounts"
                                    ><ArrowLeft class="h-4 w-4"
                                /></Link>
                            </Button>
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                            >
                                {{ t('finance.bankAccounts.detailBadge') }}
                            </div>
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ account.data.name }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ account.data.bank_name }} &middot;
                                {{ account.data.masked_account_number }}
                                &middot; {{ account.data.currency }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t('finance.bankAccounts.currentBalance')
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{
                                        formatCurrency(
                                            account.data.current_balance,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.bankAccounts.totalIncome') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{
                                        formatCurrency(
                                            account.data.total_income,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t('finance.bankAccounts.totalExpenses')
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{
                                        formatCurrency(
                                            account.data.total_expenses,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-sm">
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('finance.bankAccounts.bank') }}</span
                                >
                                <Landmark class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold">
                                {{ account.data.bank_name }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.bankAccounts.lastActivity')
                                    }}</span
                                >
                                <Wallet class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                {{
                                    account.data.last_transaction_date
                                        ? formatDate(
                                              account.data
                                                  .last_transaction_date,
                                          )
                                        : t(
                                              'finance.bankAccounts.noTransactionsYet',
                                          )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 sm:grid-cols-3">
                <KpiCard
                    :label="t('finance.bankAccounts.currentBalance')"
                    :value="formatCurrency(account.data.current_balance)"
                />
                <KpiCard
                    :label="t('finance.bankAccounts.totalIncome')"
                    :value="formatCurrency(account.data.total_income)"
                />
                <KpiCard
                    :label="t('finance.bankAccounts.totalExpenses')"
                    :value="formatCurrency(account.data.total_expenses)"
                />
            </div>

            <section
                class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.bankAccounts.history') }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold">
                            {{ t('finance.bankAccounts.historyDescription') }}
                        </h2>
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{
                            t('finance.bankAccounts.displayedItems', {
                                count: transactions.length,
                            })
                        }}
                    </div>
                </div>
                <div
                    v-if="loading"
                    class="px-6 py-8 text-center text-sm text-muted-foreground"
                >
                    {{ t('finance.bankAccounts.loadingTransactions') }}
                </div>
                <div v-else-if="transactions.length === 0">
                    <EmptyState
                        :title="
                            t('finance.bankAccounts.emptyTransactionsTitle')
                        "
                        :description="
                            t(
                                'finance.bankAccounts.emptyTransactionsDescription',
                            )
                        "
                    />
                </div>
                <div v-else class="divide-y divide-border/60">
                    <div
                        v-for="tx in transactions"
                        :key="tx.id"
                        class="flex items-center gap-4 px-6 py-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl"
                            :class="
                                tx.type === 'income'
                                    ? 'bg-green-100 dark:bg-green-900/30'
                                    : 'bg-red-100 dark:bg-red-900/30'
                            "
                        >
                            <ArrowUpCircle
                                v-if="tx.type === 'income'"
                                class="h-4 w-4 text-green-600 dark:text-green-400"
                            />
                            <ArrowDownCircle
                                v-else
                                class="h-4 w-4 text-red-600 dark:text-red-400"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{ tx.description }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    tx.category?.name ??
                                    t('common.states.uncategorized')
                                }}
                                &middot; {{ formatDate(tx.date) }}
                            </p>
                        </div>
                        <CurrencyDisplay
                            :amount="
                                tx.type === 'income' ? tx.amount : -tx.amount
                            "
                            colored
                            class="text-sm font-semibold"
                        />
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
