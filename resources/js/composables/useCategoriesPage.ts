import { computed, ref } from 'vue';
import { useCategories } from '@/composables/useCategories';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import {
    validateCategoryForm,
    type CategoryFormValues,
} from '@/lib/validation/categoryValidation';
import { useValidationErrors } from '@/composables/useValidationErrors';
import type { Category } from '@/types';

type CategoryTab = 'expense' | 'income';

type CategoryFormState = CategoryFormValues;

function createEmptyForm(type: CategoryTab = 'expense'): CategoryFormState {
    return {
        name: '',
        type,
        icon: '',
        color: '#3b82f6',
    };
}

export function useCategoriesPage(initialCategories: Category[]) {
    const { success, error: showError } = useToast();
    const { createCategory, updateCategory, deleteCategory } = useCategories();

    const categories = ref<Category[]>([...initialCategories]);
    const activeTab = ref<CategoryTab>('expense');
    const showForm = ref(false);
    const editingCategory = ref<Category | null>(null);
    const formSubmitting = ref(false);
    const deleteTarget = ref<Category | null>(null);
    const categoryForm = ref<CategoryFormState>(createEmptyForm());
    const {
        errors: formErrors,
        clearAllErrors,
        clearErrors,
        setErrors,
        setServerErrors,
    } = useValidationErrors<keyof CategoryFormState>();

    const expenseCategories = computed(() =>
        categories.value.filter((category) => category.type === 'expense'),
    );
    const incomeCategories = computed(() =>
        categories.value.filter((category) => category.type === 'income'),
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
        clearAllErrors();
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
        clearAllErrors();
        showForm.value = true;
    }

    function setCategoryForm(value: CategoryFormState) {
        const previous = categoryForm.value;
        categoryForm.value = value;

        const changedFields = (
            Object.keys(value) as (keyof CategoryFormState)[]
        ).filter((field) => previous[field] !== value[field]);

        if (changedFields.length > 0) {
            clearErrors(...changedFields);
        }
    }

    function closeForm() {
        showForm.value = false;
        clearAllErrors();
    }

    function applyPresetColor(color: string) {
        categoryForm.value.color = color;
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            clearAllErrors();

            const frontErrors = validateCategoryForm(categoryForm.value);

            if (Object.keys(frontErrors).length > 0) {
                setErrors(frontErrors);

                return;
            }

            const payload = {
                name: categoryForm.value.name,
                type: categoryForm.value.type,
                icon: categoryForm.value.icon || null,
                color: categoryForm.value.color || null,
            };

            if (editingCategory.value) {
                const updated = await updateCategory(
                    editingCategory.value.id,
                    payload,
                );
                const index = categories.value.findIndex(
                    (c) => c.id === editingCategory.value!.id,
                );

                if (index !== -1) {
                    categories.value.splice(index, 1, updated);
                }

                success(t('finance.categories.updated'));
            } else {
                const created = await createCategory(payload);
                categories.value.push(created);
                success(t('finance.categories.created'));
            }

            closeForm();
        } catch (e: any) {
            if (e.response?.status === 422) {
                setServerErrors(e.response?.data?.errors);
            } else {
                showError(t('finance.categories.saveError'));
            }
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleDelete() {
        if (!deleteTarget.value) {
            return;
        }

        try {
            const id = deleteTarget.value.id;
            await deleteCategory(id);
            categories.value = categories.value.filter((c) => c.id !== id);
            success(t('finance.categories.deleted'));
            deleteTarget.value = null;
        } catch {
            showError(t('finance.categories.deleteError'));
        }
    }

    return {
        categories,
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
        formErrors,
        deleteTarget,
        openCreate,
        openEdit,
        setCategoryForm,
        closeForm,
        applyPresetColor,
        submitForm,
        handleDelete,
    };
}
