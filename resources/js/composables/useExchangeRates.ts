import { ref } from 'vue';

import type { ExchangeRatePayload } from '@/types/api';
import type { ExchangeRate } from '@/types/models';
import api from './useApi';

export function useExchangeRates() {
    const loading = ref(false);

    async function saveRate(
        payload: ExchangeRatePayload,
    ): Promise<ExchangeRate> {
        loading.value = true;

        try {
            const { data } = await api.post('/exchange-rates', payload);

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function deleteRate(id: number): Promise<void> {
        loading.value = true;

        try {
            await api.delete(`/exchange-rates/${id}`);
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        saveRate,
        deleteRate,
    };
}
