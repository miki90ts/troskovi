<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/layouts/AppLayout.vue';
import KpiCard from '@/components/KpiCard.vue';
import LoadingSkeleton from '@/components/LoadingSkeleton.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useReports } from '@/composables/useReports';
import type { BreadcrumbItem } from '@/types';
import type { ReportSummary } from '@/types/models';
import type {
    ChartData,
    IncomeVsExpensesData,
    ReportPeriod,
} from '@/types/api';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/reports' },
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
const chartTheme = {
    mode: document.documentElement.classList.contains('dark')
        ? ('dark' as const)
        : ('light' as const),
    palette: 'palette1',
};

function barChartOptions(): ApexCharts.ApexOptions {
    if (!incomeVsExpenses.value) return {};
    return {
        chart: {
            type: 'bar' as const,
            toolbar: { show: false },
            background: 'transparent',
        },
        theme: chartTheme,
        plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
        xaxis: { categories: incomeVsExpenses.value.labels },
        colors: ['#22c55e', '#ef4444'],
        dataLabels: { enabled: false },
        legend: { position: 'top' as const },
        grid: { borderColor: 'var(--border)' },
    };
}

function barChartSeries() {
    if (!incomeVsExpenses.value) return [];
    return [
        { name: 'Income', data: incomeVsExpenses.value.income },
        { name: 'Expenses', data: incomeVsExpenses.value.expenses },
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
        theme: chartTheme,
        xaxis: { categories: netBalance.value.labels },
        colors: ['#3b82f6'],
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
    return [{ name: 'Net Balance', data: netBalance.value.values }];
}

function donutOptions(
    data: ChartData | null,
    title: string,
): ApexCharts.ApexOptions {
    if (!data) return {};
    return {
        chart: { type: 'donut' as const, background: 'transparent' },
        theme: chartTheme,
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
        theme: chartTheme,
        labels: data.labels,
        colors: ['#3b82f6', '#10b981'],
        legend: { position: 'bottom' as const },
    };
}
</script>

<template>
    <Head title="Reports" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
                    <p class="text-sm text-muted-foreground">
                        Visualize your financial data
                    </p>
                </div>
                <Tabs v-model="period">
                    <TabsList>
                        <TabsTrigger value="weekly">Weekly</TabsTrigger>
                        <TabsTrigger value="monthly">Monthly</TabsTrigger>
                        <TabsTrigger value="yearly">Yearly</TabsTrigger>
                    </TabsList>
                </Tabs>
            </div>

            <LoadingSkeleton v-if="loading" :rows="6" />

            <template v-else>
                <!-- KPIs -->
                <div
                    v-if="summary"
                    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <KpiCard
                        label="Total Income"
                        :value="
                            summary.total_income.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'USD',
                            })
                        "
                        :change="summary.mom_change"
                    />
                    <KpiCard
                        label="Total Expenses"
                        :value="
                            summary.total_expenses.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'USD',
                            })
                        "
                    />
                    <KpiCard
                        label="Net Savings"
                        :value="
                            summary.net_savings.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'USD',
                            })
                        "
                    />
                    <KpiCard
                        label="Savings Rate"
                        :value="`${summary.savings_rate}%`"
                        :subtitle="
                            summary.biggest_expense_category
                                ? `Top: ${summary.biggest_expense_category}`
                                : undefined
                        "
                    />
                </div>

                <!-- Charts Row 1: Income vs Expenses + Net Balance -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <h3 class="mb-4 font-semibold">Income vs Expenses</h3>
                        <VueApexCharts
                            v-if="incomeVsExpenses"
                            type="bar"
                            height="300"
                            :options="barChartOptions()"
                            :series="barChartSeries()"
                        />
                    </div>
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <h3 class="mb-4 font-semibold">
                            Net Balance Over Time
                        </h3>
                        <VueApexCharts
                            v-if="netBalance"
                            type="area"
                            height="300"
                            :options="lineChartOptions()"
                            :series="lineChartSeries()"
                        />
                    </div>
                </div>

                <!-- Charts Row 2: Breakdowns -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <VueApexCharts
                            v-if="expenseBreakdown"
                            type="donut"
                            height="300"
                            :options="
                                donutOptions(
                                    expenseBreakdown,
                                    'Expense Breakdown',
                                )
                            "
                            :series="expenseBreakdown.values"
                        />
                    </div>
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <VueApexCharts
                            v-if="incomeBreakdown"
                            type="donut"
                            height="300"
                            :options="
                                donutOptions(
                                    incomeBreakdown,
                                    'Income Breakdown',
                                )
                            "
                            :series="incomeBreakdown.values"
                        />
                    </div>
                    <div class="rounded-xl border bg-card p-4 shadow-sm">
                        <h3 class="mb-4 text-center font-semibold">
                            Cash vs Bank
                        </h3>
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
