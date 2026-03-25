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
import { formatCurrency, formatShortDate, t } from '@/lib/i18n';
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
    { title: t('dashboard.head'), href: '/dashboard' },
];
</script>

<template>
    <Head :title="t('dashboard.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-[2rem] border border-border/70 bg-[linear-gradient(135deg,rgba(13,148,136,0.14),rgba(255,255,255,0.95))] p-6 shadow-[0_22px_60px_rgba(15,23,42,0.08)] md:p-8 dark:bg-[linear-gradient(135deg,rgba(13,148,136,0.18),rgba(15,23,42,0.88))]"
            >
                <div
                    class="absolute -top-10 right-8 h-32 w-32 rounded-full bg-teal-300/20 blur-3xl"
                />
                <div
                    class="absolute bottom-0 left-0 h-24 w-24 rounded-full bg-emerald-300/10 blur-2xl"
                />
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-2xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            <PiggyBank class="h-3.5 w-3.5" />
                            {{ t('dashboard.badge') }}
                        </div>
                        <div class="space-y-3">
                            <h1
                                class="text-3xl font-semibold tracking-[-0.04em] text-foreground md:text-4xl"
                            >
                                {{ t('dashboard.title') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-7 text-muted-foreground md:text-base"
                            >
                                {{ t('dashboard.description') }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[360px]">
                        <Link
                            href="/reports"
                            class="group rounded-3xl border border-border/70 bg-background/75 px-5 py-4 transition hover:border-primary/30 hover:bg-background"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-semibold text-foreground"
                                    >{{ t('dashboard.reportsTitle') }}</span
                                >
                                <TrendingUp
                                    class="h-4 w-4 text-primary transition group-hover:translate-x-0.5"
                                />
                            </div>
                            <p
                                class="mt-2 text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('dashboard.reportsDescription') }}
                            </p>
                        </Link>
                        <Link
                            href="/bank-accounts"
                            class="group rounded-3xl border border-border/70 bg-background/75 px-5 py-4 transition hover:border-primary/30 hover:bg-background"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-semibold text-foreground"
                                    >{{ t('dashboard.accountsTitle') }}</span
                                >
                                <Landmark
                                    class="h-4 w-4 text-primary transition group-hover:translate-x-0.5"
                                />
                            </div>
                            <p
                                class="mt-2 text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('dashboard.accountsDescription') }}
                            </p>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- KPI Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <KpiCard
                    :label="t('dashboard.kpis.totalIncome')"
                    :value="formatCurrency(summary.total_income)"
                    :change="summary.mom_change"
                    :subtitle="t('dashboard.kpis.thisMonth')"
                />
                <KpiCard
                    :label="t('dashboard.kpis.totalExpenses')"
                    :value="formatCurrency(summary.total_expenses)"
                    :subtitle="t('dashboard.kpis.thisMonth')"
                />
                <KpiCard
                    :label="t('dashboard.kpis.netSavings')"
                    :value="formatCurrency(summary.net_savings)"
                    :subtitle="t('dashboard.kpis.thisMonth')"
                />
                <KpiCard
                    :label="t('dashboard.kpis.savingsRate')"
                    :value="`${summary.savings_rate}%`"
                    :subtitle="
                        summary.biggest_expense_category
                            ? t('dashboard.kpis.topExpense', {
                                  value: summary.biggest_expense_category,
                              })
                            : undefined
                    "
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Transactions -->
                <div
                    class="rounded-3xl border border-border/70 bg-card/95 shadow-[0_16px_40px_rgba(15,23,42,0.06)] lg:col-span-2"
                >
                    <div
                        class="flex items-center justify-between border-b border-border/70 px-6 py-5"
                    >
                        <div>
                            <h2 class="font-semibold">
                                {{ t('dashboard.recentTransactions.title') }}
                            </h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{
                                    t(
                                        'dashboard.recentTransactions.description',
                                    )
                                }}
                            </p>
                        </div>
                        <Link
                            href="/expenses"
                            class="flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                        >
                            {{ t('dashboard.recentTransactions.viewAll') }}
                            <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div
                        v-if="recentTransactions.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        {{ t('dashboard.recentTransactions.empty') }}
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
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30'
                                        : 'bg-rose-100 dark:bg-rose-900/30'
                                "
                            >
                                <ArrowUpCircle
                                    v-if="tx.type === 'income'"
                                    class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                                />
                                <ArrowDownCircle
                                    v-else
                                    class="h-4 w-4 text-rose-600 dark:text-rose-400"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ tx.description }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        tx.category?.name ??
                                        t(
                                            'dashboard.recentTransactions.uncategorized',
                                        )
                                    }}
                                    &middot; {{ formatShortDate(tx.date) }}
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
                <div
                    class="rounded-3xl border border-border/70 bg-card/95 shadow-[0_16px_40px_rgba(15,23,42,0.06)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-border/70 px-6 py-5"
                    >
                        <div>
                            <h2 class="font-semibold">
                                {{ t('dashboard.accounts.title') }}
                            </h2>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ t('dashboard.accounts.description') }}
                            </p>
                        </div>
                        <Link
                            href="/bank-accounts"
                            class="flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                        >
                            {{ t('dashboard.accounts.manage') }}
                            <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div
                        v-if="accounts.length === 0"
                        class="px-6 py-12 text-center text-sm text-muted-foreground"
                    >
                        {{ t('dashboard.accounts.empty') }}
                    </div>
                    <div v-else class="divide-y">
                        <Link
                            v-for="account in accounts"
                            :key="account.id"
                            :href="`/bank-accounts/${account.id}`"
                            class="flex items-center gap-3 px-6 py-3 transition-colors hover:bg-muted/40"
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
