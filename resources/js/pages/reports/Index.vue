<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarRange,
    CircleDollarSign,
    Landmark,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import VueApexCharts from 'vue3-apexcharts';
import KpiCard from '@/components/KpiCard.vue';
import LoadingSkeleton from '@/components/LoadingSkeleton.vue';
import ReportChartCard from '@/components/reports/ReportChartCard.vue';
import ReportsBudgetSection from '@/components/reports/ReportsBudgetSection.vue';
import ReportsHeroSection from '@/components/reports/ReportsHeroSection.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { useReportsPage } from '@/composables/useReportsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.reports'), href: '/reports' },
];

const {
    period,
    budgetPeriod,
    loading,
    budgetLoading,
    summary,
    incomeVsExpenses,
    netBalance,
    expenseBreakdown,
    incomeBreakdown,
    cashVsBank,
    budgetProgress,
    formattedPeriodRange,
    barChartOptions,
    barChartSeries,
    lineChartOptions,
    lineChartSeries,
    donutOptions,
    pieOptions,
} = useReportsPage();
</script>

<template>
    <Head :title="t('finance.reports.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <ReportsHeroSection
                :period="period"
                :formatted-period-range="formattedPeriodRange"
                :summary="summary"
                @update:period="period = $event"
            />

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
                    <ReportChartCard
                        :eyebrow="t('finance.reports.basicComparison')"
                        :title="t('finance.reports.incomeVsExpenses')"
                        :icon="CircleDollarSign"
                    >
                        <VueApexCharts
                            v-if="incomeVsExpenses"
                            type="bar"
                            height="300"
                            :options="barChartOptions()"
                            :series="barChartSeries()"
                        />
                    </ReportChartCard>
                    <ReportChartCard
                        :eyebrow="t('finance.reports.balanceTrend')"
                        :title="t('finance.reports.netBalanceOverTime')"
                        :icon="CalendarRange"
                    >
                        <VueApexCharts
                            v-if="netBalance"
                            type="area"
                            height="300"
                            :options="lineChartOptions()"
                            :series="lineChartSeries()"
                        />
                    </ReportChartCard>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <ReportChartCard
                        :eyebrow="t('finance.reports.structure')"
                        :title="t('finance.reports.expensesCategories')"
                        :icon="TrendingDown"
                    >
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
                    </ReportChartCard>
                    <ReportChartCard
                        :eyebrow="t('finance.reports.structure')"
                        :title="t('finance.reports.incomeCategories')"
                        :icon="TrendingUp"
                    >
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
                    </ReportChartCard>
                    <ReportChartCard
                        :eyebrow="t('finance.reports.paymentStructure')"
                        :title="t('finance.reports.cashVsBank')"
                        :icon="Landmark"
                    >
                        <VueApexCharts
                            v-if="cashVsBank"
                            type="pie"
                            height="260"
                            :options="pieOptions(cashVsBank)"
                            :series="cashVsBank.values"
                        />
                    </ReportChartCard>
                </div>

                <ReportsBudgetSection
                    :budget-period="budgetPeriod"
                    :budget-loading="budgetLoading"
                    :budget-progress="budgetProgress"
                    @update:budget-period="budgetPeriod = $event"
                />
            </template>
        </div>
    </AppLayout>
</template>
