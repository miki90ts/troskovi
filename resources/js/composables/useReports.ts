import { ref } from 'vue';
import api from './useApi';

import type {
    ChartData,
    IncomeVsExpensesData,
    ReportPeriod,
} from '@/types/api';
import type { ReportSummary } from '@/types/models';

export function useReports() {
    const loading = ref(false);

    async function fetchSummary(
        period: ReportPeriod = 'monthly',
    ): Promise<ReportSummary> {
        loading.value = true;

        try {
            const { data } = await api.get('/reports/summary', {
                params: { period },
            });

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchIncomeVsExpenses(
        period: ReportPeriod = 'monthly',
    ): Promise<IncomeVsExpensesData> {
        const { data } = await api.get('/reports/income-vs-expenses', {
            params: { period },
        });

        return data.data;
    }

    async function fetchNetBalance(
        period: ReportPeriod = 'monthly',
    ): Promise<ChartData> {
        const { data } = await api.get('/reports/net-balance', {
            params: { period },
        });

        return data.data;
    }

    async function fetchExpenseBreakdown(
        period: ReportPeriod = 'monthly',
    ): Promise<ChartData> {
        const { data } = await api.get('/reports/expense-breakdown', {
            params: { period },
        });

        return data.data;
    }

    async function fetchIncomeBreakdown(
        period: ReportPeriod = 'monthly',
    ): Promise<ChartData> {
        const { data } = await api.get('/reports/income-breakdown', {
            params: { period },
        });

        return data.data;
    }

    async function fetchCashVsBank(
        period: ReportPeriod = 'monthly',
    ): Promise<ChartData> {
        const { data } = await api.get('/reports/cash-vs-bank', {
            params: { period },
        });

        return data.data;
    }

    return {
        loading,
        fetchSummary,
        fetchIncomeVsExpenses,
        fetchNetBalance,
        fetchExpenseBreakdown,
        fetchIncomeBreakdown,
        fetchCashVsBank,
    };
}
