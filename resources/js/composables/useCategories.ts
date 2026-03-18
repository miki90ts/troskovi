import { ref } from 'vue';
import api from './useApi';

import type { Category } from '@/types/models';

export function useCategories() {
    const loading = ref(false);

    async function fetchCategories(
        type?: 'income' | 'expense',
    ): Promise<Category[]> {
        loading.value = true;

        try {
            const { data } = await api.get('/categories', {
                params: type ? { type } : {},
            });

            return data.data;
        } finally {
            loading.value = false;
        }
    }

    async function createCategory(
        payload: Partial<Category>,
    ): Promise<Category> {
        const { data } = await api.post('/categories', payload);

        return data.data;
    }

    async function updateCategory(
        id: number,
        payload: Partial<Category>,
    ): Promise<Category> {
        const { data } = await api.put(`/categories/${id}`, payload);

        return data.data;
    }

    async function deleteCategory(id: number): Promise<void> {
        await api.delete(`/categories/${id}`);
    }

    return {
        loading,
        fetchCategories,
        createCategory,
        updateCategory,
        deleteCategory,
    };
}
