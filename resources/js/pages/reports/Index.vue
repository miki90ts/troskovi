<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import {
    CalendarRange,
    CircleDollarSign,
    Landmark,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/layouts/AppLayout.vue';
import KpiCard from '@/components/KpiCard.vue';
import LoadingSkeleton from '@/components/LoadingSkeleton.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useReports } from '@/composables/useReports';
import { formatCurrency, t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { ReportSummary } from '@/types/models';
import type {
    ChartData,
    IncomeVsExpensesData,
    ReportPeriod,
} from '@/types/api';

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.reports'), href: '/reports' },
];

const {
    fetchSummary,
    fetchIncomeVsExpenses,
    fetchNetBalance,
    fetchExpenseBreakdown,
    fetchIncomeBreakdown,
    fetchCashVsBank,
} = useReports();

const period = ref<ReportPeriod>('monthly');
const loading = ref(true);

const summary = ref<ReportSummary | null>(null);
const incomeVsExpenses = ref<IncomeVsExpensesData | null>(null);
const netBalance = ref<ChartData | null>(null);
const expenseBreakdown = ref<ChartData | null>(null);
const incomeBreakdown = ref<ChartData | null>(null);
const cashVsBank = ref<ChartData | null>(null);

const periodLabels: Record<ReportPeriod, string> = {
    weekly: t('common.recurringFrequencies.weekly'),
    monthly: t('common.recurringFrequencies.monthly'),
    yearly: t('common.recurringFrequencies.yearly'),
};

const formattedPeriodRange = computed(() => {
    if (!summary.value) return t('finance.reports.currentPeriod');
    return `${new Date(summary.value.period_start).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })} - ${new Date(summary.value.period_end).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })}`;
});

async function loadData() {
    loading.value = true;
    try {
        const [s, ive, nb, eb, ib, cvb] = await Promise.all([
            fetchSummary(period.value),
            fetchIncomeVsExpenses(period.value),
            fetchNetBalance(period.value),
            fetchExpenseBreakdown(period.value),
            fetchIncomeBreakdown(period.value),
            fetchCashVsBank(period.value),
        ]);
        summary.value = s;
        incomeVsExpenses.value = ive;
        netBalance.value = nb;
        expenseBreakdown.value = eb;
        incomeBreakdown.value = ib;
        cashVsBank.value = cvb;
    } finally {
        loading.value = false;
    }
}

onMounted(loadData);
watch(period, loadData);

// Chart options
function chartTheme() {
    return {
        mode:
            typeof document !== 'undefined' &&
            document.documentElement.classList.contains('dark')
                ? ('dark' as const)
                : ('light' as const),
        palette: 'palette1',
    };
}

function barChartOptions(): ApexCharts.ApexOptions {
    if (!incomeVsExpenses.value) return {};
    return {
        chart: {
            type: 'bar' as const,
            toolbar: { show: false },
            background: 'transparent',
        },
        theme: chartTheme(),
        plotOptions: { bar: { columnWidth: '58%', borderRadius: 10 } },
        xaxis: { categories: incomeVsExpenses.value.labels },
        colors: ['#14b8a6', '#f97316'],
        dataLabels: { enabled: false },
        legend: { position: 'top' as const },
        grid: { borderColor: 'var(--border)' },
    };
}

function barChartSeries() {
    if (!incomeVsExpenses.value) return [];
    return [
        {
            name: t('finance.reports.income'),
            data: incomeVsExpenses.value.income,
        },
        {
            name: t('finance.reports.expenses'),
            data: incomeVsExpenses.value.expenses,
        },
    ];
}

function lineChartOptions(): ApexCharts.ApexOptions {
    if (!netBalance.value) return {};
    return {
        chart: {
            type: 'area' as const,
            toolbar: { show: false },
            background: 'transparent',
        },
        theme: chartTheme(),
        xaxis: { categories: netBalance.value.labels },
        colors: ['#14b8a6'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' as const, width: 2 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: 0.4, opacityTo: 0 },
        },
        grid: { borderColor: 'var(--border)' },
    };
}

function lineChartSeries() {
    if (!netBalance.value) return [];
    return [
        {
            name: t('finance.reports.netBalance'),
            data: netBalance.value.values,
        },
    ];
}

function donutOptions(
    data: ChartData | null,
    title: string,
): ApexCharts.ApexOptions {
    if (!data) return {};
    return {
        chart: { type: 'donut' as const, background: 'transparent' },
        theme: chartTheme(),
        labels: data.labels,
        colors: data.colors?.length ? data.colors : undefined,
        legend: { position: 'bottom' as const },
        title: {
            text: title,
            align: 'center' as const,
            style: { fontSize: '14px' },
        },
        plotOptions: { pie: { donut: { size: '65%' } } },
    };
}

function pieOptions(data: ChartData | null): ApexCharts.ApexOptions {
    if (!data) return {};
    return {
        chart: { type: 'pie' as const, background: 'transparent' },
        theme: chartTheme(),
        labels: data.labels,
        colors: ['#14b8a6', '#0f766e'],
        legend: { position: 'bottom' as const },
    };
}
</script>

<template>
    <Head :title="t('finance.reports.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.16),transparent_42%),linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,253,245,0.9))] p-6 shadow-sm dark:bg-[radial-gradient(circle_at_top_left,rgba(20,184,166,0.22),transparent_38%),linear-gradient(135deg,rgba(15,23,42,0.96),rgba(13,148,136,0.14))]"
            >
                <div
                    class="absolute inset-y-0 right-0 hidden w-1/3 bg-[radial-gradient(circle_at_center,rgba(45,212,191,0.16),transparent_62%)] lg:block"
                />
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-2xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.reports.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.reports.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('finance.reports.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.reportRhythm') }}
                                </p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{ periodLabels[period] }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.coveredPeriod') }}
                                </p>
                                <p class="mt-2 text-sm leading-6 font-semibold">
                                    {{ formattedPeriodRange }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.monthlyChange') }}
                                </p>
                                <p class="mt-2 text-2xl font-semibold">
                                    {{
                                        summary
                                            ? `${summary.mom_change}%`
                                            : '...'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 lg:items-end">
                        <Tabs v-model="period">
                            <TabsList
                                class="grid h-auto grid-cols-3 rounded-2xl border border-border/60 bg-background/85 p-1"
                            >
                                <TabsTrigger
                                    value="weekly"
                                    class="rounded-xl px-4 py-2.5"
                                    >{{
                                        t('common.recurringFrequencies.weekly')
                                    }}</TabsTrigger
                                >
                                <TabsTrigger
                                    value="monthly"
                                    class="rounded-xl px-4 py-2.5"
                                    >{{
                                        t('common.recurringFrequencies.monthly')
                                    }}</TabsTrigger
                                >
                                <TabsTrigger
                                    value="yearly"
                                    class="rounded-xl px-4 py-2.5"
                                    >Godisnje</TabsTrigger
                                >
                            </TabsList>
                        </Tabs>
                        <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-100">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                        >{{
                                            t('finance.reports.biggestExpense')
                                        }}</span
                                    >
                                    <TrendingDown
                                        class="h-4 w-4 text-primary"
                                    />
                                </div>
                                <p class="mt-3 text-base font-semibold">
                                    {{
                                        summary?.biggest_expense_category ||
                                        t('common.states.noDataYet')
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{
                                        summary
                                            ? formatCurrency(
                                                  summary.biggest_expense_amount,
                                              )
                                            : t('common.states.waitingData')
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                        >{{
                                            t('finance.reports.savingsRate')
                                        }}</span
                                    >
                                    <TrendingUp class="h-4 w-4 text-primary" />
                                </div>
                                <p class="mt-3 text-base font-semibold">
                                    {{
                                        summary
                                            ? t('finance.reports.retained', {
                                                  value: summary.savings_rate,
                                              })
                                            : t('common.states.waitingData')
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{
                                        t('finance.reports.currentPeriodNote', {
                                            period: periodLabels[
                                                period
                                            ].toLowerCase(),
                                        })
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <LoadingSkeleton v-if="loading" :rows="6" />

            <template v-else>
                <div
                    v-if="summary"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <KpiCard
                        :label="t('finance.reports.totalIncome')"
                        :value="
                            summary.total_income.toLocaleString('sr-RS', {
                                style: 'currency',
                                currency: 'RSD',
                            })
                        "
                        :change="summary.mom_change"
                    />
                    <KpiCard
                        :label="t('finance.reports.totalExpenses')"
                        :value="
                            summary.total_expenses.toLocaleString('sr-RS', {
                                style: 'currency',
                                currency: 'RSD',
                            })
                        "
                        :subtitle="summary.biggest_expense_category"
                    />
                    <KpiCard
                        :label="t('finance.reports.netSavings')"
                        :value="
                            summary.net_savings.toLocaleString('sr-RS', {
                                style: 'currency',
                                currency: 'RSD',
                            })
                        "
                        :subtitle="formattedPeriodRange"
                    />
                    <KpiCard
                        :label="t('finance.reports.savingsRateKpi')"
                        :value="`${summary.savings_rate}%`"
                        :subtitle="
                            summary.biggest_expense_category
                                ? t('finance.reports.top', {
                                      value: summary.biggest_expense_category,
                                  })
                                : undefined
                        "
                    />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div
                        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.basicComparison') }}
                                </p>
                                <h3 class="mt-1 font-semibold">
                                    {{ t('finance.reports.incomeVsExpenses') }}
                                </h3>
                            </div>
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <CircleDollarSign class="h-4 w-4" />
                            </div>
                        </div>
                        <VueApexCharts
                            v-if="incomeVsExpenses"
                            type="bar"
                            height="300"
                            :options="barChartOptions()"
                            :series="barChartSeries()"
                        />
                    </div>
                    <div
                        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.balanceTrend') }}
                                </p>
                                <h3 class="mt-1 font-semibold">
                                    {{
                                        t('finance.reports.netBalanceOverTime')
                                    }}
                                </h3>
                            </div>
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <CalendarRange class="h-4 w-4" />
                            </div>
                        </div>
                        <VueApexCharts
                            v-if="netBalance"
                            type="area"
                            height="300"
                            :options="lineChartOptions()"
                            :series="lineChartSeries()"
                        />
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.structure') }}
                                </p>
                                <h3 class="mt-1 font-semibold">
                                    {{
                                        t('finance.reports.expensesCategories')
                                    }}
                                </h3>
                            </div>
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <TrendingDown class="h-4 w-4" />
                            </div>
                        </div>
                        <VueApexCharts
                            v-if="expenseBreakdown"
                            type="donut"
                            height="300"
                            :options="
                                donutOptions(
                                    expenseBreakdown,
                                    t('finance.reports.expenseStructure'),
                                )
                            "
                            :series="expenseBreakdown.values"
                        />
                    </div>
                    <div
                        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.structure') }}
                                </p>
                                <h3 class="mt-1 font-semibold">
                                    {{ t('finance.reports.incomeCategories') }}
                                </h3>
                            </div>
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <TrendingUp class="h-4 w-4" />
                            </div>
                        </div>
                        <VueApexCharts
                            v-if="incomeBreakdown"
                            type="donut"
                            height="300"
                            :options="
                                donutOptions(
                                    incomeBreakdown,
                                    t('finance.reports.incomeStructure'),
                                )
                            "
                            :series="incomeBreakdown.values"
                        />
                    </div>
                    <div
                        class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 flex items-start justify-between gap-4"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.reports.paymentStructure') }}
                                </p>
                                <h3 class="mt-1 font-semibold">
                                    {{ t('finance.reports.cashVsBank') }}
                                </h3>
                            </div>
                            <div
                                class="rounded-2xl bg-primary/10 p-2 text-primary"
                            >
                                <Landmark class="h-4 w-4" />
                            </div>
                        </div>
                        <VueApexCharts
                            v-if="cashVsBank"
                            type="pie"
                            height="260"
                            :options="pieOptions(cashVsBank)"
                            :series="cashVsBank.values"
                        />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
