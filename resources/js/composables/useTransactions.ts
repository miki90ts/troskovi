import { ref } from 'vue';
import api from './useApi';

import type { PaginatedResponse, TransactionFilters } from '@/types/api';
import type { Transaction } from '@/types/models';

export function useTransactions() {
    const loading = ref(false);

    async function fetchTransactions(
        filters: TransactionFilters = {},
    ): Promise<PaginatedResponse<Transaction>> {
        loading.value = true;

        try {
            const { data } = await api.get('/transactions', {
                params: filters,
            });

            return data;
        } finally {
            loading.value = false;
        }
    }

    async function createTransaction(
        payload: FormData | Record<string, unknown>,
    ): Promise<Transaction> {
        const isFormData = payload instanceof FormData;
        const { data } = await api.post('/transactions', payload, {
            headers: isFormData
                ? { 'Content-Type': 'multipart/form-data' }
                : {},
        });

        return data.data;
    }

    async function updateTransaction(
        id: number,
        payload: FormData | Record<string, unknown>,
    ): Promise<Transaction> {
        const isFormData = payload instanceof FormData;

        if (isFormData) {
            payload.append('_method', 'PUT');
        }

        const { data } = await api.post(
            `/transactions/${id}`,
            isFormData ? payload : { ...payload, _method: 'PUT' },
            {
                headers: isFormData
                    ? { 'Content-Type': 'multipart/form-data' }
                    : {},
            },
        );

        return data.data;
    }

    async function deleteTransaction(id: number): Promise<void> {
        await api.delete(`/transactions/${id}`);
    }

    return {
        loading,
        fetchTransactions,
        createTransaction,
        updateTransaction,
        deleteTransaction,
    };
}
