<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Check,
    Palette,
    Pencil,
    Plus,
    Tags,
    Trash2,
} from 'lucide-vue-next';
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
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Category } from '@/types/models';

const props = defineProps<{
    categories: { data: Category[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.categories'), href: '/categories' },
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
const activeCategories = computed(() =>
    activeTab.value === 'expense'
        ? expenseCategories.value
        : incomeCategories.value,
);
const systemCount = computed(
    () =>
        activeCategories.value.filter((category) => category.is_system).length,
);
const customCount = computed(
    () =>
        activeCategories.value.filter((category) => !category.is_system).length,
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

// Delete
const deleteTarget = ref<Category | null>(null);

async function handleDelete() {
    if (!deleteTarget.value) return;
    try {
        await deleteCategory(deleteTarget.value.id);
        success(t('finance.categories.deleted'));
        deleteTarget.value = null;
        router.reload();
    } catch {
        showError(t('finance.categories.deleteError'));
    }
}
</script>

<template>
    <Head :title="t('finance.categories.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-6 shadow-sm"
            >
                <div
                    class="absolute -top-16 -left-12 h-48 w-48 rounded-full bg-primary/15 blur-3xl"
                />
                <div
                    class="absolute right-0 bottom-0 hidden h-56 w-56 rounded-full bg-emerald-300/10 blur-3xl lg:block"
                />
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-2xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.categories.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.categories.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('finance.categories.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t('finance.categories.totalCategories')
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ props.categories.data.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.categories.expenseGroups') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ expenseCategories.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.categories.incomeGroups') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ incomeCategories.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-md">
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.categories.activeView')
                                    }}</span
                                >
                                <Tags class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold capitalize">
                                {{ activeTab }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('finance.categories.system') }}</span
                                >
                                <Palette class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold">
                                {{ systemCount }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('finance.categories.custom') }}</span
                                >
                                <Plus class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold">
                                {{ customCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
            >
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.categories.managementTitle') }}
                        </p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight">
                            {{ t('finance.categories.managementDescription') }}
                        </h2>
                    </div>
                    <Button class="h-11 rounded-2xl px-5" @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('finance.categories.add') }}
                    </Button>
                </div>

                <Tabs v-model="activeTab" class="mt-5 space-y-5">
                    <TabsList
                        class="grid h-auto w-full grid-cols-2 rounded-2xl border border-border/60 bg-background p-1 md:w-fit"
                    >
                        <TabsTrigger
                            value="expense"
                            class="rounded-xl px-4 py-2.5"
                        >
                            {{
                                t('finance.categories.expenseTab', {
                                    count: expenseCategories.length,
                                })
                            }}
                        </TabsTrigger>
                        <TabsTrigger
                            value="income"
                            class="rounded-xl px-4 py-2.5"
                        >
                            {{
                                t('finance.categories.incomeTab', {
                                    count: incomeCategories.length,
                                })
                            }}
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="expense" class="mt-0">
                        <div
                            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ activeTabLabel }}
                                </p>
                                <h3
                                    class="mt-1 text-lg font-semibold tracking-tight"
                                >
                                    {{
                                        t(
                                            'finance.categories.expenseSectionDescription',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div
                                class="flex items-center gap-3 text-sm text-muted-foreground"
                            >
                                <span>{{
                                    t('finance.categories.totalCount', {
                                        count: expenseCategories.length,
                                    })
                                }}</span>
                                <span
                                    class="hidden h-1 w-1 rounded-full bg-border sm:block"
                                />
                                <span
                                    >{{
                                        expenseCategories.length - systemCount
                                    }}
                                    {{
                                        t('finance.categories.editableCount', {
                                            count:
                                                expenseCategories.length -
                                                systemCount,
                                        })
                                    }}</span
                                >
                            </div>
                        </div>
                        <div v-if="expenseCategories.length === 0">
                            <EmptyState
                                :title="
                                    t('finance.categories.emptyExpenseTitle')
                                "
                                :description="
                                    t(
                                        'finance.categories.emptyExpenseDescription',
                                    )
                                "
                            >
                                <Button
                                    class="rounded-2xl px-5"
                                    @click="openCreate"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    {{ t('finance.categories.add') }}
                                </Button>
                            </EmptyState>
                        </div>
                        <div
                            v-else
                            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="cat in expenseCategories"
                                :key="cat.id"
                                class="group flex min-h-32 items-center justify-between rounded-3xl border border-border/60 bg-background/80 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-semibold shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                (cat.color ?? '#6b7280') + '20',
                                            color: cat.color ?? '#6b7280',
                                        }"
                                    >
                                        {{ cat.icon ?? cat.name.charAt(0) }}
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">
                                                {{ cat.name }}
                                            </p>
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[11px] font-medium text-red-600 dark:border-red-400/20 dark:bg-red-500/10 dark:text-red-300"
                                            >
                                                <ArrowDownCircle
                                                    class="h-3 w-3"
                                                />
                                                {{
                                                    t(
                                                        'finance.categories.expenseLabel',
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <p
                                            v-if="cat.is_system"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'finance.categories.systemCategory',
                                                )
                                            }}
                                        </p>
                                        <p
                                            v-else
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'finance.categories.customCategory',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="!cat.is_system" class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl"
                                        @click="openEdit(cat)"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl text-destructive"
                                        @click="deleteTarget = cat"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="income" class="mt-0">
                        <div
                            class="mb-4 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ activeTabLabel }}
                                </p>
                                <h3
                                    class="mt-1 text-lg font-semibold tracking-tight"
                                >
                                    {{
                                        t(
                                            'finance.categories.incomeSectionDescription',
                                        )
                                    }}
                                </h3>
                            </div>
                            <div
                                class="flex items-center gap-3 text-sm text-muted-foreground"
                            >
                                <span>{{
                                    t('finance.categories.totalCount', {
                                        count: incomeCategories.length,
                                    })
                                }}</span>
                                <span
                                    class="hidden h-1 w-1 rounded-full bg-border sm:block"
                                />
                                <span
                                    >{{ incomeCategories.length - systemCount }}
                                    {{
                                        t('finance.categories.editableCount', {
                                            count:
                                                incomeCategories.length -
                                                systemCount,
                                        })
                                    }}</span
                                >
                            </div>
                        </div>
                        <div v-if="incomeCategories.length === 0">
                            <EmptyState
                                :title="
                                    t('finance.categories.emptyIncomeTitle')
                                "
                                :description="
                                    t(
                                        'finance.categories.emptyIncomeDescription',
                                    )
                                "
                            >
                                <Button
                                    class="rounded-2xl px-5"
                                    @click="openCreate"
                                >
                                    <Plus class="mr-2 h-4 w-4" />
                                    {{ t('finance.categories.add') }}
                                </Button>
                            </EmptyState>
                        </div>
                        <div
                            v-else
                            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="cat in incomeCategories"
                                :key="cat.id"
                                class="group flex min-h-32 items-center justify-between rounded-3xl border border-border/60 bg-background/80 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-semibold shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                (cat.color ?? '#6b7280') + '20',
                                            color: cat.color ?? '#6b7280',
                                        }"
                                    >
                                        {{ cat.icon ?? cat.name.charAt(0) }}
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">
                                                {{ cat.name }}
                                            </p>
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-600 dark:border-emerald-400/20 dark:bg-emerald-500/10 dark:text-emerald-300"
                                            >
                                                <ArrowUpCircle
                                                    class="h-3 w-3"
                                                />
                                                {{
                                                    t(
                                                        'finance.categories.incomeLabel',
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <p
                                            v-if="cat.is_system"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'finance.categories.systemCategory',
                                                )
                                            }}
                                        </p>
                                        <p
                                            v-else
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                t(
                                                    'finance.categories.customCategory',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="!cat.is_system" class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl"
                                        @click="openEdit(cat)"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-9 w-9 rounded-2xl text-destructive"
                                        @click="deleteTarget = cat"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </section>
        </div>

        <Dialog
            :open="showForm"
            @update:open="
                (v: boolean) => {
                    if (!v) showForm = false;
                }
            "
        >
            <DialogContent
                class="rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-xl"
            >
                <DialogHeader>
                    <div
                        class="relative overflow-hidden border-b border-border/60 bg-card px-6 py-5"
                    >
                        <div
                            class="absolute -top-10 left-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"
                        />
                        <div
                            class="absolute right-4 bottom-0 h-24 w-24 rounded-full bg-emerald-300/10 blur-3xl"
                        />
                        <div
                            class="relative inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.categories.formBadge') }}
                        </div>
                        <DialogTitle
                            class="relative mt-4 text-2xl tracking-tight"
                        >
                            {{
                                editingCategory
                                    ? t('finance.categories.editTitle')
                                    : t('finance.categories.newTitle')
                            }}
                        </DialogTitle>
                        <DialogDescription
                            class="relative mt-2 max-w-lg text-sm leading-6"
                        >
                            {{
                                editingCategory
                                    ? t('finance.categories.editDescription')
                                    : t('finance.categories.createDescription')
                            }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <form class="space-y-6 px-6 py-6" @submit.prevent="submitForm">
                    <div class="grid gap-2">
                        <Label
                            for="cat_name"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.categories.name') }}
                        </Label>
                        <Input
                            id="cat_name"
                            v-model="categoryForm.name"
                            :placeholder="
                                t('finance.categories.namePlaceholder')
                            "
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                                >{{ t('common.labels.type') }}</Label
                            >
                            <Select
                                v-model="categoryForm.type"
                                :disabled="!!editingCategory"
                            >
                                <SelectTrigger
                                    class="h-11 rounded-2xl border-border/60 bg-background"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="expense">{{
                                        t('finance.categories.expenseLabel')
                                    }}</SelectItem>
                                    <SelectItem value="income">{{
                                        t('finance.categories.incomeLabel')
                                    }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label
                                for="cat_icon"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >
                                {{ t('finance.categories.icon') }}
                            </Label>
                            <Input
                                id="cat_icon"
                                v-model="categoryForm.icon"
                                placeholder="🛒"
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            />
                        </div>
                    </div>

                    <div
                        class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end"
                    >
                        <div class="grid gap-2">
                            <Label
                                for="cat_color"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >
                                {{ t('finance.categories.color') }}
                            </Label>
                            <div
                                class="rounded-3xl border border-dashed border-border/70 bg-muted/20 px-4 py-3"
                            >
                                <p class="text-sm font-medium">
                                    {{ t('finance.categories.visualPreview') }}
                                </p>
                                <div class="mt-3 flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-semibold shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                (categoryForm.color ||
                                                    '#3b82f6') + '20',
                                            color:
                                                categoryForm.color || '#3b82f6',
                                        }"
                                    >
                                        {{
                                            categoryForm.icon ||
                                            categoryForm.name.charAt(0) ||
                                            '?'
                                        }}
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{
                                                categoryForm.name ||
                                                t(
                                                    'finance.categories.categoryNameFallback',
                                                )
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground capitalize"
                                        >
                                            {{
                                                categoryForm.type === 'expense'
                                                    ? t(
                                                          'finance.categories.expenseCategory',
                                                      )
                                                    : t(
                                                          'finance.categories.incomeCategory',
                                                      )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="cat_color"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >
                                {{ t('finance.categories.chooseColor') }}
                            </Label>
                            <Input
                                id="cat_color"
                                v-model="categoryForm.color"
                                type="color"
                                class="h-14 w-full rounded-2xl border-border/60 bg-background p-2 md:w-24"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.categories.quickChoices') }}
                        </Label>
                        <div
                            class="flex flex-wrap gap-2 rounded-3xl border border-dashed border-border/70 bg-muted/20 p-3"
                        >
                            <button
                                v-for="preset in colorPresets"
                                :key="preset"
                                type="button"
                                class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/40 shadow-sm transition hover:scale-105"
                                :style="{ backgroundColor: preset }"
                                @click="applyPresetColor(preset)"
                            >
                                <Check
                                    v-if="categoryForm.color === preset"
                                    class="h-4 w-4 text-white"
                                />
                            </button>
                        </div>
                    </div>

                    <DialogFooter class="border-t border-border/60 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="rounded-2xl"
                            @click="showForm = false"
                        >
                            {{ t('common.actions.cancel') }}
                        </Button>
                        <Button
                            class="rounded-2xl px-5"
                            type="submit"
                            :disabled="formSubmitting"
                        >
                            {{
                                formSubmitting
                                    ? t('finance.bankAccounts.saving')
                                    : editingCategory
                                      ? t('common.actions.update')
                                      : t('common.actions.create')
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :open="!!deleteTarget"
            :title="t('finance.categories.deleteTitle')"
            :description="t('finance.categories.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
