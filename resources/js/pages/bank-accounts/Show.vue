<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDownCircle, ArrowLeft, ArrowUpCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import KpiCard from '@/components/KpiCard.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useTransactions } from '@/composables/useTransactions';
import { Button } from '@/components/ui/button';
import type { BreadcrumbItem } from '@/types';
import type { BankAccountOverview, Transaction } from '@/types/models';
import type { PaginatedResponse } from '@/types/api';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    account: { data: BankAccountOverview };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Bank Accounts', href: '/bank-accounts' },
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
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}
</script>

<template>
    <Head :title="account.data.name" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/bank-accounts"
                        ><ArrowLeft class="h-4 w-4"
                    /></Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ account.data.name }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ account.data.bank_name }} &middot;
                        {{ account.data.masked_account_number }}
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <KpiCard
                    label="Current Balance"
                    :value="
                        account.data.current_balance.toLocaleString('en-US', {
                            style: 'currency',
                            currency: account.data.currency,
                        })
                    "
                />
                <KpiCard
                    label="Total Income"
                    :value="
                        account.data.total_income.toLocaleString('en-US', {
                            style: 'currency',
                            currency: account.data.currency,
                        })
                    "
                />
                <KpiCard
                    label="Total Expenses"
                    :value="
                        account.data.total_expenses.toLocaleString('en-US', {
                            style: 'currency',
                            currency: account.data.currency,
                        })
                    "
                />
            </div>

            <!-- Transactions for this account -->
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="border-b px-6 py-4">
                    <h2 class="font-semibold">Account Transactions</h2>
                </div>
                <div
                    v-if="loading"
                    class="px-6 py-8 text-center text-sm text-muted-foreground"
                >
                    Loading...
                </div>
                <div v-else-if="transactions.length === 0">
                    <EmptyState
                        title="No transactions"
                        description="No transactions recorded for this account yet."
                    />
                </div>
                <div v-else class="divide-y">
                    <div
                        v-for="tx in transactions"
                        :key="tx.id"
                        class="flex items-center gap-4 px-6 py-3"
                    >
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
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
                                    tx.category?.name ?? 'Uncategorized'
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
            </div>
        </div>
    </AppLayout>
</template>
