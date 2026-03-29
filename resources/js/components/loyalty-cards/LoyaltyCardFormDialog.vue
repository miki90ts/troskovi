<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import BarcodeDisplay from '@/components/loyalty-cards/BarcodeDisplay.vue';
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
import type { LoyaltyCard } from '@/types/models';

const props = defineProps<{
    open: boolean;
    editingCard: LoyaltyCard | null;
    formSubmitting: boolean;
    colorPresets: string[];
    form: {
        name: string;
        card_number: string;
        notes: string;
        color: string;
    };
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:form': [
        value: {
            name: string;
            card_number: string;
            notes: string;
            color: string;
        },
    ];
    submit: [];
    close: [];
    applyPresetColor: [color: string];
}>();

function updateForm(
    patch: Partial<{
        name: string;
        card_number: string;
        notes: string;
        color: string;
    }>,
) {
    emit('update:form', {
        ...props.form,
        ...patch,
    });
}

const nameModel = computed({
    get: () => props.form.name,
    set: (value: string) => updateForm({ name: value }),
});

const cardNumberModel = computed({
    get: () => props.form.card_number,
    set: (value: string) => updateForm({ card_number: value }),
});

const notesModel = computed({
    get: () => props.form.notes,
    set: (value: string) => updateForm({ notes: value }),
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
                        {{ t('loyaltyCards.formBadge') }}
                    </div>
                    <DialogTitle class="relative mt-4 text-2xl tracking-tight">
                        {{
                            props.editingCard
                                ? t('loyaltyCards.editTitle')
                                : t('loyaltyCards.newTitle')
                        }}
                    </DialogTitle>
                    <DialogDescription
                        class="relative mt-2 max-w-lg text-sm leading-6"
                    >
                        {{
                            props.editingCard
                                ? t('loyaltyCards.editDescription')
                                : t('loyaltyCards.createDescription')
                        }}
                    </DialogDescription>
                </div>
            </DialogHeader>

            <form class="space-y-6 px-6 py-6" @submit.prevent="emit('submit')">
                <div class="grid gap-2">
                    <Label
                        for="lc_name"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('loyaltyCards.cardName') }}
                    </Label>
                    <Input
                        id="lc_name"
                        v-model="nameModel"
                        :placeholder="t('loyaltyCards.cardNamePlaceholder')"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label
                        for="lc_number"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('loyaltyCards.cardNumber') }}
                    </Label>
                    <Input
                        id="lc_number"
                        v-model="cardNumberModel"
                        :placeholder="t('loyaltyCards.cardNumberPlaceholder')"
                        class="h-11 rounded-2xl border-border/60 bg-background"
                        required
                    />
                </div>

                <!-- Live barcode preview -->
                <div
                    v-if="props.form.card_number.trim()"
                    class="rounded-2xl border border-dashed border-border/70 bg-white p-4"
                >
                    <p
                        class="mb-2 text-center text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('loyaltyCards.barcodePreview') }}
                    </p>
                    <BarcodeDisplay
                        :value="props.form.card_number"
                        :width="1.5"
                        :height="60"
                        :font-size="14"
                    />
                </div>

                <div class="grid gap-2">
                    <Label
                        for="lc_notes"
                        class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        {{ t('loyaltyCards.notes') }}
                    </Label>
                    <textarea
                        id="lc_notes"
                        v-model="notesModel"
                        :placeholder="t('loyaltyCards.notesPlaceholder')"
                        rows="3"
                        class="flex w-full rounded-2xl border border-border/60 bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    />
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('loyaltyCards.color') }}
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
                    <div class="grid gap-2">
                        <Label
                            for="lc_color"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            {{ t('loyaltyCards.chooseColor') }}
                        </Label>
                        <Input
                            id="lc_color"
                            v-model="colorModel"
                            type="color"
                            class="h-14 w-full rounded-2xl border-border/60 bg-background p-2 md:w-24"
                        />
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
                                : props.editingCard
                                  ? t('common.actions.update')
                                  : t('common.actions.create')
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
