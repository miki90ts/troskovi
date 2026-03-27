import { ref } from 'vue';

import type { SpendingTargetPayload } from '@/types/api';
import type {
    SpendingTarget,
    SpendingTargetPeriod,
    SpendingTargetProgressResponse,
} from '@/types/models';
import api from './useApi';

export function useSpendingTargets() {
    const loading = ref(false);

    async function fetchTargets(): Promise<SpendingTarget[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/spending-targets');

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createTarget(
        payload: SpendingTargetPayload,
    ): Promise<SpendingTarget> {
        const { data } = await api.post('/spending-targets', payload);

        return data.data;
    }

    async function updateTarget(
        id: number,
        payload: Partial<SpendingTargetPayload>,
    ): Promise<SpendingTarget> {
        const { data } = await api.put(`/spending-targets/${id}`, payload);

        return data.data;
    }

    async function deleteTarget(id: number): Promise<void> {
        await api.delete(`/spending-targets/${id}`);
    }

    async function fetchProgress(
        period: SpendingTargetPeriod = 'monthly',
    ): Promise<SpendingTargetProgressResponse> {
        const { data } = await api.get('/reports/spending-progress', {
            params: { period },
        });

        return data.data;
    }

    return {
        loading,
        fetchTargets,
        createTarget,
        updateTarget,
        deleteTarget,
        fetchProgress,
    };
}
