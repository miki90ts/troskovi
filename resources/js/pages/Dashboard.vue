<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowRight,
    ArrowUpCircle,
    Landmark,
    PiggyBank,
    TrendingUp,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import KpiCard from '@/components/KpiCard.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import type { BreadcrumbItem } from '@/types';
import type {
    BankAccountSummary,
    DashboardTransaction,
    ReportSummary,
} from '@/types/models';

const props = defineProps<{
    summary: ReportSummary;
    recentTransactions: DashboardTransaction[];
    accounts: BankAccountSummary[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- KPI Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <KpiCard
                    label="Total Income"
                    :value="
                        summary.total_income.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                        })
                    "
                    :change="summary.mom_change"
                    subtitle="This month"
                />
                <KpiCard
                    label="Total Expenses"
                    :value="
                        summary.total_expenses.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                        })
                    "
                    subtitle="This month"
                />
                <KpiCard
                    label="Net Savings"
                    :value="
                        summary.net_savings.toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'USD',
                        })
                    "
                    subtitle="This month"
                />
                <KpiCard
                    label="Savings Rate"
                    :value="`${summary.savings_rate}%`"
                    :subtitle="
                        summary.biggest_expense_category
                            ? `Top expense: ${summary.biggest_expense_category}`
                            : undefined
                    "
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Transactions -->
                <div class="rounded-xl border bg-card shadow-sm lg:col-span-2">
                    <div
                        class="flex items-center justify-between border-b px-6 py-4"
                    >
                        <h2 class="font-semibold">Recent Transactions</h2>
                        <Link
                            href="/expenses"
                            class="flex items-center gap-1 text-sm text-primary hover:underline"
                        >
                            View all <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div
                        v-if="recentTransactions.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        No transactions yet. Add your first one!
                    </div>
                    <div v-else class="divide-y">
                        <div
                            v-for="tx in recentTransactions"
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
                                    tx.type === 'income'
                                        ? tx.amount
                                        : -tx.amount
                                "
                                colored
                                class="text-sm font-semibold"
                            />
                        </div>
                    </div>
                </div>

                <!-- Bank Accounts -->
                <div class="rounded-xl border bg-card shadow-sm">
                    <div
                        class="flex items-center justify-between border-b px-6 py-4"
                    >
                        <h2 class="font-semibold">Bank Accounts</h2>
                        <Link
                            href="/bank-accounts"
                            class="flex items-center gap-1 text-sm text-primary hover:underline"
                        >
                            Manage <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div
                        v-if="accounts.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        No bank accounts added yet.
                    </div>
                    <div v-else class="divide-y">
                        <Link
                            v-for="account in accounts"
                            :key="account.id"
                            :href="`/bank-accounts/${account.id}`"
                            class="flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/50"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                                :style="{
                                    backgroundColor: account.color
                                        ? account.color + '20'
                                        : undefined,
                                }"
                            >
                                <Landmark
                                    class="h-4 w-4"
                                    :style="{
                                        color: account.color ?? undefined,
                                    }"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ account.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ account.bank_name }}
                                </p>
                            </div>
                            <CurrencyDisplay
                                :amount="account.current_balance"
                                :currency="account.currency"
                                colored
                                class="text-sm font-semibold"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
