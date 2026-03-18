import { ref } from 'vue';
import api from './useApi';

import type { BankAccount, BankAccountOverview } from '@/types/models';

export function useBankAccounts() {
    const loading = ref(false);

    async function fetchAccounts(
        includeArchived = false,
    ): Promise<BankAccount[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/bank-accounts', {
                params: { include_archived: includeArchived ? 1 : 0 },
            });

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function fetchOverview(id: number): Promise<BankAccountOverview> {
        const { data } = await api.get(`/bank-accounts/${id}/overview`);

        return data.data;
    }

    async function createAccount(
        payload: Partial<BankAccount>,
    ): Promise<BankAccount> {
        const { data } = await api.post('/bank-accounts', payload);

        return data.data;
    }

    async function updateAccount(
        id: number,
        payload: Partial<BankAccount>,
    ): Promise<BankAccount> {
        const { data } = await api.put(`/bank-accounts/${id}`, payload);

        return data.data;
    }

    async function archiveAccount(id: number): Promise<void> {
        await api.delete(`/bank-accounts/${id}`);
    }

    async function restoreAccount(id: number): Promise<BankAccount> {
        const { data } = await api.post(`/bank-accounts/${id}/restore`);

        return data.data;
    }

    async function transferFunds(payload: {
        from_account_id: number;
        to_account_id: number;
        amount: number;
        description?: string;
        date: string;
    }) {
        const { data } = await api.post('/bank-accounts/transfer', payload);

        return data.data;
    }

    return {
        loading,
        fetchAccounts,
        fetchOverview,
        createAccount,
        updateAccount,
        archiveAccount,
        restoreAccount,
        transferFunds,
    };
}
