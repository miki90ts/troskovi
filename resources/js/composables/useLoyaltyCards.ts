import { ref } from 'vue';

import type { LoyaltyCardPayload } from '@/types/api';
import type { LoyaltyCard } from '@/types/models';
import api from './useApi';

export function useLoyaltyCards() {
    const loading = ref(false);

    async function fetchCards(): Promise<LoyaltyCard[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/loyalty-cards');

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createCard(
        payload: LoyaltyCardPayload,
    ): Promise<LoyaltyCard> {
        const { data } = await api.post('/loyalty-cards', payload);

        return data.data;
    }

    async function updateCard(
        id: number,
        payload: Partial<LoyaltyCardPayload>,
    ): Promise<LoyaltyCard> {
        const { data } = await api.put(`/loyalty-cards/${id}`, payload);

        return data.data;
    }

    async function deleteCard(id: number): Promise<void> {
        await api.delete(`/loyalty-cards/${id}`);
    }

    return {
        loading,
        fetchCards,
        createCard,
        updateCard,
        deleteCard,
    };
}
