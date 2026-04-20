<script setup lang="ts">
import { computed } from 'vue';
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
import { Label } from '@/components/ui/label';
import { t } from '@/lib/i18n';
import type { Debt } from '@/types/models';

const props = defineProps<{
    open: boolean;
    editingDebt: Debt | null;
    formSubmitting: boolean;
    form: {
        type: 'i_owe' | 'owed_to_me';
        person_name: string;
        description: string;
        amount: string;
        date: string;
        due_date: string;
        notes: string;
    };
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [
        value: {
            type: 'i_owe' | 'owed_to_me';
            person_name: string;
            description: string;
            amount: string;
            date: string;
            due_date: string;
            notes: string;
        },
    ];
    submit: [];
    close: [];
}>();

function updateForm(
    patch: Partial<{
        type: 'i_owe' | 'owed_to_me';
        person_name: string;
        description: string;
        amount: string;
        date: string;
        due_date: string;
        notes: string;
    }>,
) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

const personNameModel = computed({
    get: () => props.form.person_name,
    set: (value: string) => updateForm({ person_name: value }),
});
const descriptionModel = computed({
    get: () => props.form.description,
    set: (value: string) => updateForm({ description: value }),
});
const amountModel = computed({
    get: () => props.form.amount,
    set: (value: string) => updateForm({ amount: value }),
});
const dateModel = computed({
    get: () => props.form.date,
    set: (value: string) => updateForm({ date: value }),
});
const dueDateModel = computed({
    get: () => props.form.due_date,
    set: (value: string) => updateForm({ due_date: value }),
});
const notesModel = computed({
    get: () => props.form.notes,
    set: (value: string) => updateForm({ notes: value }),
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
                        {{ t('debts.formBadge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{
                            props.editingDebt
                                ? t('debts.editTitle')
                                : t('debts.newTitle')
                        }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{
                            props.editingDebt
                                ? t('debts.editDescription')
                                : t('debts.createDescription')
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <div class="grid gap-2">
                    <Label
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('debts.typeLabel') }}
                    </Label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="flex flex-col items-center gap-1 rounded-2xl border p-3 text-sm font-medium transition"
                            :class="
                                props.form.type === 'i_owe'
                                    ? 'border-orange-500/40 bg-orange-500/10 text-orange-700'
                                    : 'border-border/60 bg-background text-muted-foreground hover:border-border'
                            "
                            :disabled="!!props.editingDebt"
                            @click="updateForm({ type: 'i_owe' })"
                        >
                            {{ t('debts.iOweLabel') }}
                            <span class="text-xs font-normal opacity-70">
                                {{ t('debts.iOweSubtitle') }}
                            </span>
                        </button>
                        <button
                            type="button"
                            class="flex flex-col items-center gap-1 rounded-2xl border p-3 text-sm font-medium transition"
                            :class="
                                props.form.type === 'owed_to_me'
                                    ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700'
                                    : 'border-border/60 bg-background text-muted-foreground hover:border-border'
                            "
                            :disabled="!!props.editingDebt"
                            @click="updateForm({ type: 'owed_to_me' })"
                        >
                            {{ t('debts.owedToMeLabel') }}
                            <span class="text-xs font-normal opacity-70">
                                {{ t('debts.owedToMeSubtitle') }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label
                            for="debt_person"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('debts.personName') }}
                        </Label>
                        <Input
                            id="debt_person"
                            v-model="personNameModel"
                            :placeholder="t('debts.personNamePlaceholder')"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="debt_amount"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('common.labels.amount') }}
                        </Label>
                        <Input
                            id="debt_amount"
                            v-model="amountModel"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :placeholder="t('debts.amountPlaceholder')"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label
                        for="debt_description"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.description') }}
                    </Label>
                    <Input
                        id="debt_description"
                        v-model="descriptionModel"
                        :placeholder="t('debts.descriptionPlaceholder')"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label
                            for="debt_date"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('common.labels.date') }}
                        </Label>
                        <Input
                            id="debt_date"
                            v-model="dateModel"
                            type="date"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="debt_due_date"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('debts.dueDate') }}
                        </Label>
                        <Input
                            id="debt_due_date"
                            v-model="dueDateModel"
                            type="date"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label
                        for="debt_notes"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.notes') }}
                    </Label>
                    <textarea
                        id="debt_notes"
                        v-model="notesModel"
                        :placeholder="t('debts.notesPlaceholder')"
                        class="min-h-[80px] w-full rounded-2xl border border-border/60 bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
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
                                : props.editingDebt
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
