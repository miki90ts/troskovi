import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useCategories } from '@/composables/useCategories';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { Category } from '@/types';

type CategoryTab = 'expense' | 'income';

type CategoryFormState = {
    name: string;
    type: CategoryTab;
    icon: string;
    color: string;
};

function createEmptyForm(type: CategoryTab = 'expense'): CategoryFormState {
    return {
        name: '',
        type,
        icon: '',
        color: '#3b82f6',
    };
}

export function useCategoriesPage(categories: Category[]) {
    const { success, error: showError } = useToast();
    const { createCategory, updateCategory, deleteCategory } = useCategories();

    const activeTab = ref<CategoryTab>('expense');
    const showForm = ref(false);
    const editingCategory = ref<Category | null>(null);
    const formSubmitting = ref(false);
    const deleteTarget = ref<Category | null>(null);
    const categoryForm = ref<CategoryFormState>(createEmptyForm());

    const expenseCategories = computed(() =>
        categories.filter((category) => category.type === 'expense'),
    );
    const incomeCategories = computed(() =>
        categories.filter((category) => category.type === 'income'),
    );
    const activeCategories = computed(() =>
        activeTab.value === 'expense'
            ? expenseCategories.value
            : incomeCategories.value,
    );
    const systemCount = computed(
        () =>
            activeCategories.value.filter((category) => category.is_system)
                .length,
    );
    const customCount = computed(
        () =>
            activeCategories.value.filter((category) => !category.is_system)
                .length,
    );
    const activeTabLabel = computed(() =>
        activeTab.value === 'expense'
            ? t('finance.categories.expenseCollection')
            : t('finance.categories.incomeCollection'),
    );

    const colorPresets = [
        '#14b8a6',
        '#10b981',
        '#3b82f6',
        '#f97316',
        '#ef4444',
        '#8b5cf6',
    ];

    function openCreate() {
        editingCategory.value = null;
        categoryForm.value = createEmptyForm(activeTab.value);
        showForm.value = true;
    }

    function openEdit(category: Category) {
        editingCategory.value = category;
        categoryForm.value = {
            name: category.name,
            type: category.type,
            icon: category.icon ?? '',
            color: category.color ?? '#3b82f6',
        };
        showForm.value = true;
    }

    function applyPresetColor(color: string) {
        categoryForm.value.color = color;
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            const payload = {
                name: categoryForm.value.name,
                type: categoryForm.value.type,
                icon: categoryForm.value.icon || null,
                color: categoryForm.value.color || null,
            };

            if (editingCategory.value) {
                await updateCategory(editingCategory.value.id, payload);
                success(t('finance.categories.updated'));
            } else {
                await createCategory(payload);
                success(t('finance.categories.created'));
            }

            showForm.value = false;
            router.reload();
        } catch {
            showError(t('finance.categories.saveError'));
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleDelete() {
        if (!deleteTarget.value) {
            return;
        }

        try {
            await deleteCategory(deleteTarget.value.id);
            success(t('finance.categories.deleted'));
            deleteTarget.value = null;
            router.reload();
        } catch {
            showError(t('finance.categories.deleteError'));
        }
    }

    return {
        activeTab,
        expenseCategories,
        incomeCategories,
        activeCategories,
        systemCount,
        customCount,
        activeTabLabel,
        colorPresets,
        showForm,
        editingCategory,
        formSubmitting,
        categoryForm,
        deleteTarget,
        openCreate,
        openEdit,
        applyPresetColor,
        submitForm,
        handleDelete,
    };
}
