<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useToast } from '@/composables/useToast';
import { useCategories } from '@/composables/useCategories';
import type { BreadcrumbItem } from '@/types';
import type { Category } from '@/types/models';

const props = defineProps<{
    categories: { data: Category[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/categories' },
];

const { success, error: showError } = useToast();
const { createCategory, updateCategory, deleteCategory } = useCategories();

const activeTab = ref<'expense' | 'income'>('expense');

const expenseCategories = computed(() =>
    props.categories.data.filter((c) => c.type === 'expense'),
);
const incomeCategories = computed(() =>
    props.categories.data.filter((c) => c.type === 'income'),
);

// Form
const showForm = ref(false);
const editingCategory = ref<Category | null>(null);
const formSubmitting = ref(false);
const categoryForm = ref({
    name: '',
    type: 'expense' as 'income' | 'expense',
    icon: '',
    color: '#3b82f6',
});

function openCreate() {
    editingCategory.value = null;
    categoryForm.value = {
        name: '',
        type: activeTab.value,
        icon: '',
        color: '#3b82f6',
    };
    showForm.value = true;
}

function openEdit(cat: Category) {
    editingCategory.value = cat;
    categoryForm.value = {
        name: cat.name,
        type: cat.type,
        icon: cat.icon ?? '',
        color: cat.color ?? '#3b82f6',
    };
    showForm.value = true;
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
            success('Category updated');
        } else {
            await createCategory(payload);
            success('Category created');
        }
        showForm.value = false;
        router.reload();
    } catch {
        showError('Failed to save category');
    } finally {
        formSubmitting.value = false;
    }
}

// Delete
const deleteTarget = ref<Category | null>(null);

async function handleDelete() {
    if (!deleteTarget.value) return;
    try {
        await deleteCategory(deleteTarget.value.id);
        success('Category deleted');
        deleteTarget.value = null;
        router.reload();
    } catch {
        showError('Failed to delete category');
    }
}

function CategoryList(categories: Category[]) {
    return categories;
}
</script>

<template>
    <Head title="Categories" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Categories
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Organize your transactions by category
                    </p>
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Add Category
                </Button>
            </div>

            <Tabs v-model="activeTab">
                <TabsList>
                    <TabsTrigger value="expense">
                        Expense ({{ expenseCategories.length }})
                    </TabsTrigger>
                    <TabsTrigger value="income">
                        Income ({{ incomeCategories.length }})
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="expense">
                    <div v-if="expenseCategories.length === 0">
                        <EmptyState
                            title="No expense categories"
                            description="Add categories to organize your expenses."
                        />
                    </div>
                    <div
                        v-else
                        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="cat in expenseCategories"
                            :key="cat.id"
                            class="flex items-center justify-between rounded-lg border bg-card p-4"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm"
                                    :style="{
                                        backgroundColor:
                                            (cat.color ?? '#6b7280') + '20',
                                        color: cat.color ?? '#6b7280',
                                    }"
                                >
                                    {{ cat.icon ?? cat.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ cat.name }}</p>
                                    <p
                                        v-if="cat.is_system"
                                        class="text-xs text-muted-foreground"
                                    >
                                        System
                                    </p>
                                </div>
                            </div>
                            <div v-if="!cat.is_system" class="flex gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7"
                                    @click="openEdit(cat)"
                                >
                                    <Pencil class="h-3 w-3" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7 text-destructive"
                                    @click="deleteTarget = cat"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="income">
                    <div v-if="incomeCategories.length === 0">
                        <EmptyState
                            title="No income categories"
                            description="Add categories to organize your income."
                        />
                    </div>
                    <div
                        v-else
                        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="cat in incomeCategories"
                            :key="cat.id"
                            class="flex items-center justify-between rounded-lg border bg-card p-4"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm"
                                    :style="{
                                        backgroundColor:
                                            (cat.color ?? '#6b7280') + '20',
                                        color: cat.color ?? '#6b7280',
                                    }"
                                >
                                    {{ cat.icon ?? cat.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ cat.name }}</p>
                                    <p
                                        v-if="cat.is_system"
                                        class="text-xs text-muted-foreground"
                                    >
                                        System
                                    </p>
                                </div>
                            </div>
                            <div v-if="!cat.is_system" class="flex gap-1">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7"
                                    @click="openEdit(cat)"
                                >
                                    <Pencil class="h-3 w-3" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-7 w-7 text-destructive"
                                    @click="deleteTarget = cat"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Category Form Dialog -->
        <Dialog
            :open="showForm"
            @update:open="
                (v: boolean) => {
                    if (!v) showForm = false;
                }
            "
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        >{{
                            editingCategory ? 'Edit' : 'New'
                        }}
                        Category</DialogTitle
                    >
                    <DialogDescription>{{
                        editingCategory
                            ? 'Update category details.'
                            : 'Create a new category.'
                    }}</DialogDescription>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="grid gap-2">
                        <Label for="cat_name">Name</Label>
                        <Input
                            id="cat_name"
                            v-model="categoryForm.name"
                            placeholder="e.g. Groceries"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Type</Label>
                        <Select
                            v-model="categoryForm.type"
                            :disabled="!!editingCategory"
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="expense">Expense</SelectItem>
                                <SelectItem value="income">Income</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="cat_icon">Icon (emoji)</Label>
                            <Input
                                id="cat_icon"
                                v-model="categoryForm.icon"
                                placeholder="🛒"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="cat_color">Color</Label>
                            <Input
                                id="cat_color"
                                v-model="categoryForm.color"
                                type="color"
                                class="h-10 p-1"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showForm = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="formSubmitting">
                            {{
                                formSubmitting
                                    ? 'Saving...'
                                    : editingCategory
                                      ? 'Update'
                                      : 'Create'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirm -->
        <ConfirmDialog
            :open="!!deleteTarget"
            title="Delete Category"
            description="Transactions using this category will become uncategorized."
            confirm-text="Delete"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
