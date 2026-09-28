import { ref } from 'vue';
import type { DebtPayload } from '@/types/api';
import type { Debt, DebtSummary } from '@/types/models';
import api from './useApi';

export function useDebts() {
    const loading = ref(false);

    async function fetchDebts(
        params?: Record<string, string>,
    ): Promise<Debt[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/debts', { params });

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createDebt(payload: DebtPayload): Promise<Debt> {
        const { data } = await api.post('/debts', payload);

        return data.data;
    }

    async function updateDebt(
        id: number,
        payload: Partial<DebtPayload>,
    ): Promise<Debt> {
        const { data } = await api.put(`/debts/${id}`, payload);

        return data.data;
    }

    async function deleteDebt(id: number): Promise<void> {
        await api.delete(`/debts/${id}`);
    }

    async function fetchDebtSummary(): Promise<DebtSummary> {
        const { data } = await api.get('/debts/summary');

        return data;
    }

    return {
        loading,
        fetchDebts,
        createDebt,
        updateDebt,
        deleteDebt,
        fetchDebtSummary,
    };
}
