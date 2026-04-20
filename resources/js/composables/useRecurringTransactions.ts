import { ref } from 'vue';
import type { RecurringTransaction } from '@/types/models';
import api from './useApi';


export function useRecurringTransactions() {
    const loading = ref(false);

    async function fetchRecurring(): Promise<RecurringTransaction[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/recurring-transactions');

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createRecurring(
        payload: Partial<RecurringTransaction>,
    ): Promise<RecurringTransaction> {
        const { data } = await api.post('/recurring-transactions', payload);

        return data.data;
    }

    async function updateRecurring(
        id: number,
        payload: Partial<RecurringTransaction>,
    ): Promise<RecurringTransaction> {
        const { data } = await api.put(
            `/recurring-transactions/${id}`,
            payload,
        );

        return data.data;
    }

    async function deleteRecurring(id: number): Promise<void> {
        await api.delete(`/recurring-transactions/${id}`);
    }

    return {
        loading,
        fetchRecurring,
        createRecurring,
        updateRecurring,
        deleteRecurring,
    };
}
