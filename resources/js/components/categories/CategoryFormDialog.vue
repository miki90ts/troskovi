<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type {
    CategoryFormErrors,
    CategoryFormValues,
} from '@/lib/validation/categoryValidation';
import type { Category } from '@/types';

const props = defineProps<{
    open: boolean;
    editingCategory: Category | null;
    formSubmitting: boolean;
    colorPresets: string[];
    form: CategoryFormValues;
    errors: CategoryFormErrors;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [value: CategoryFormValues];
    submit: [];
    close: [];
    applyPresetColor: [color: string];
}>();

function updateForm(patch: Partial<CategoryFormValues>) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

function fieldErrorClass(field: keyof CategoryFormValues): string {
    return props.errors[field]
        ? 'border-destructive focus-visible:ring-destructive/20'
        : '';
}

const nameModel = computed({
    get: () => props.form.name,
    set: (value: string) => updateForm({ name: value }),
});

const typeModel = computed({
    get: () => props.form.type,
    set: (value: 'expense' | 'income') => updateForm({ type: value }),
});

const iconModel = computed({
    get: () => props.form.icon,
    set: (value: string) => updateForm({ icon: value }),
});

const colorModel = computed({
    get: () => props.form.color,
    set: (value: string) => updateForm({ color: value }),
});
</script>

<template>
    <Dialog
        :open="props.open"
        @update:open="(value) => emit('update:open', value)"
    >
        <DialogContent
            class="max-h-[90vh] overflow-y-auto rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-xl"
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
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{
                            props.editingCategory
                                ? t('finance.categories.editTitle')
                                : t('finance.categories.newTitle')
                        }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{
                            props.editingCategory
                                ? t('finance.categories.editDescription')
                                : t('finance.categories.createDescription')
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <FormField
                    :label="t('finance.categories.name')"
                    field-id="cat_name"
                    :error="props.errors.name"
                >
                    <template #default>
                        <Input
                            id="cat_name"
                            v-model="nameModel"
                            :placeholder="
                                t('finance.categories.namePlaceholder')
                            "
                            :class="[
                                'h-11 rounded-2xl border-border/60 bg-background',
                                fieldErrorClass('name'),
                            ]"
                        />
                    </template>
                </FormField>

                <div class="grid gap-4 md:grid-cols-2">
                    <FormField
                        :label="t('common.labels.type')"
                        :error="props.errors.type"
                    >
                        <template #default>
                            <Select
                                v-model="typeModel"
                                :disabled="!!props.editingCategory"
                            >
                                <SelectTrigger
                                    :class="[
                                        'h-11 rounded-2xl border-border/60 bg-background',
                                        fieldErrorClass('type'),
                                    ]"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="expense">
                                        {{
                                            t('finance.categories.expenseLabel')
                                        }}
                                    </SelectItem>
                                    <SelectItem value="income">
                                        {{
                                            t('finance.categories.incomeLabel')
                                        }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </template>
                    </FormField>

                    <FormField
                        :label="t('finance.categories.icon')"
                        field-id="cat_icon"
                        :error="props.errors.icon"
                    >
                        <template #default>
                            <Input
                                id="cat_icon"
                                v-model="iconModel"
                                placeholder="🛒"
                                :class="[
                                    'h-11 rounded-2xl border-border/60 bg-background',
                                    fieldErrorClass('icon'),
                                ]"
                            />
                        </template>
                    </FormField>
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
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
                                            (props.form.color || '#3b82f6') +
                                            '20',
                                        color: props.form.color || '#3b82f6',
                                    }"
                                >
                                    {{
                                        props.form.icon ||
                                        props.form.name.charAt(0) ||
                                        '?'
                                    }}
                                </div>
                                <div>
                                    <p class="font-medium">
                                        {{
                                            props.form.name ||
                                            t(
                                                'finance.categories.categoryNameFallback',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-xs text-muted-foreground capitalize"
                                    >
                                        {{
                                            props.form.type === 'expense'
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
                    <FormField
                        :label="t('finance.categories.chooseColor')"
                        field-id="cat_color"
                        :error="props.errors.color"
                    >
                        <template #default>
                            <Input
                                id="cat_color"
                                v-model="colorModel"
                                type="color"
                                :class="[
                                    'h-14 w-full rounded-2xl border-border/60 bg-background p-2 md:w-24',
                                    fieldErrorClass('color'),
                                ]"
                            />
                        </template>
                    </FormField>
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
                            v-for="preset in props.colorPresets"
                            :key="preset"
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/40 shadow-sm transition hover:scale-105"
                            :style="{ backgroundColor: preset }"
                            @click="emit('applyPresetColor', preset)"
                        >
                            <Check
                                v-if="props.form.color === preset"
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
                        @click="emit('close')"
                    >
                        {{ t('common.actions.cancel') }}
                    </Button>
                    <Button
                        class="rounded-2xl px-5"
                        type="submit"
                        :disabled="props.formSubmitting"
                    >
                        {{
                            props.formSubmitting
                                ? t('finance.bankAccounts.saving')
                                : props.editingCategory
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
