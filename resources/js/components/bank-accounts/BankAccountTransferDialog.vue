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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    open: boolean;
    submitting: boolean;
    activeAccounts: BankAccount[];
    form: {
        from_account_id: string;
        to_account_id: string;
        amount: string;
        description: string;
    };
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [
        value: {
            from_account_id: string;
            to_account_id: string;
            amount: string;
            description: string;
        },
    ];
    submit: [];
    close: [];
}>();

function updateForm(
    patch: Partial<{
        from_account_id: string;
        to_account_id: string;
        amount: string;
        description: string;
    }>,
) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

const fromAccountModel = computed({
    get: () => props.form.from_account_id,
    set: (value: string) => updateForm({ from_account_id: value }),
});

const toAccountModel = computed({
    get: () => props.form.to_account_id,
    set: (value: string) => updateForm({ to_account_id: value }),
});

const amountModel = computed({
    get: () => props.form.amount,
    set: (value: string) => updateForm({ amount: value }),
});

const descriptionModel = computed({
    get: () => props.form.description,
    set: (value: string) => updateForm({ description: value }),
});

const availableToAccounts = computed(() =>
    props.activeAccounts.filter(
        (a) => String(a.id) !== props.form.from_account_id,
    ),
);
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
                        {{ t('finance.bankAccounts.transferBadge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{ t('finance.bankAccounts.transferTitle') }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{ t('finance.bankAccounts.transferDescription') }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <div class="grid gap-2">
                    <Label
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.fromAccount') }}
                    </Label>
                    <Select v-model="fromAccountModel">
                        <SelectTrigger
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        >
                            <SelectValue
                                :placeholder="
                                    t('finance.bankAccounts.selectAccount')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="a in activeAccounts"
                                :key="a.id"
                                :value="String(a.id)"
                            >
                                {{ a.name }} ({{ a.currency }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.toAccount') }}
                    </Label>
                    <Select v-model="toAccountModel">
                        <SelectTrigger
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        >
                            <SelectValue
                                :placeholder="
                                    t('finance.bankAccounts.selectAccount')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="a in availableToAccounts"
                                :key="a.id"
                                :value="String(a.id)"
                            >
                                {{ a.name }} ({{ a.currency }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label
                        for="transfer_amount"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('common.labels.amount') }}
                    </Label>
                    <Input
                        id="transfer_amount"
                        v-model="amountModel"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label
                        for="transfer_desc"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('finance.bankAccounts.transferDescriptionLabel') }}
                    </Label>
                    <Input
                        id="transfer_desc"
                        v-model="descriptionModel"
                        :placeholder="
                            t(
                                'finance.bankAccounts.transferDescriptionPlaceholder',
                            )
                        "
                        class="h-11 rounded-2xl border-border/60 bg-background"
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
                        :disabled="props.submitting"
                    >
                        {{
                            props.submitting
                                ? t('finance.bankAccounts.transfering')
                                : t('common.actions.transfer')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
