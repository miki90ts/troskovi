import { computed, onMounted, ref, watch } from 'vue';
import { useReportCharts } from '@/composables/useReportCharts';
import { useReports } from '@/composables/useReports';
import { useSpendingTargets } from '@/composables/useSpendingTargets';
import { t } from '@/lib/i18n';
import type {
    ChartData,
    IncomeVsExpensesData,
    ReportPeriod,
    ReportSummary,
    SpendingTargetPeriod,
    SpendingTargetProgressResponse,
} from '@/types';

export function useReportsPage() {
    const {
        fetchSummary,
        fetchIncomeVsExpenses,
        fetchNetBalance,
        fetchExpenseBreakdown,
        fetchIncomeBreakdown,
        fetchCashVsBank,
    } = useReports();
    const { fetchProgress } = useSpendingTargets();
    const charts = useReportCharts();

    const period = ref<ReportPeriod>('monthly');
    const budgetPeriod = ref<SpendingTargetPeriod>('monthly');
    const loading = ref(true);
    const budgetLoading = ref(true);

    const summary = ref<ReportSummary | null>(null);
    const incomeVsExpenses = ref<IncomeVsExpensesData | null>(null);
    const netBalance = ref<ChartData | null>(null);
    const expenseBreakdown = ref<ChartData | null>(null);
    const incomeBreakdown = ref<ChartData | null>(null);
    const cashVsBank = ref<ChartData | null>(null);
    const budgetProgress = ref<SpendingTargetProgressResponse | null>(null);

    const formattedPeriodRange = computed(() => {
        if (!summary.value) {
            return t('finance.reports.currentPeriod');
        }

        return `${new Date(summary.value.period_start).toLocaleDateString(
            'sr-RS',
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            },
        )} - ${new Date(summary.value.period_end).toLocaleDateString('sr-RS', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        })}`;
    });

    async function loadData() {
        loading.value = true;

        try {
            const [
                nextSummary,
                nextIncomeVsExpenses,
                nextNetBalance,
                nextExpenseBreakdown,
                nextIncomeBreakdown,
                nextCashVsBank,
            ] = await Promise.all([
                fetchSummary(period.value),
                fetchIncomeVsExpenses(period.value),
                fetchNetBalance(period.value),
                fetchExpenseBreakdown(period.value),
                fetchIncomeBreakdown(period.value),
                fetchCashVsBank(period.value),
            ]);

            summary.value = nextSummary;
            incomeVsExpenses.value = nextIncomeVsExpenses;
            netBalance.value = nextNetBalance;
            expenseBreakdown.value = nextExpenseBreakdown;
            incomeBreakdown.value = nextIncomeBreakdown;
            cashVsBank.value = nextCashVsBank;
        } finally {
            loading.value = false;
        }
    }

    async function loadBudgetProgress() {
        budgetLoading.value = true;

        try {
            budgetProgress.value = await fetchProgress(budgetPeriod.value);
        } finally {
            budgetLoading.value = false;
        }
    }

    onMounted(() => {
        loadData();
        loadBudgetProgress();
    });

    watch(period, loadData);
    watch(budgetPeriod, loadBudgetProgress);

    return {
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
        barChartOptions: () => charts.barChartOptions(incomeVsExpenses.value),
        barChartSeries: () => charts.barChartSeries(incomeVsExpenses.value),
        lineChartOptions: () => charts.lineChartOptions(netBalance.value),
        lineChartSeries: () => charts.lineChartSeries(netBalance.value),
        donutOptions: (data: ChartData | null, title: string) =>
            charts.donutOptions(data, title),
        pieOptions: (data: ChartData | null) => charts.pieOptions(data),
    };
}
