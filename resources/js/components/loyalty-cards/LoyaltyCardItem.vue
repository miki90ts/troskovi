<script setup lang="ts">
import { Maximize2, Pencil, StickyNote, Trash2 } from 'lucide-vue-next';
import BarcodeDisplay from '@/components/loyalty-cards/BarcodeDisplay.vue';
import { Button } from '@/components/ui/button';
import { t } from '@/lib/i18n';
import type { LoyaltyCard } from '@/types/models';

const props = defineProps<{
    card: LoyaltyCard;
}>();

const emit = defineEmits<{
    edit: [card: LoyaltyCard];
    delete: [card: LoyaltyCard];
    fullscreen: [card: LoyaltyCard];
}>();

const cardColor = computed(() => props.card.color ?? '#14b8a6');
</script>

<script lang="ts">
import { computed } from 'vue';
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-3xl border border-border/60 bg-background/80 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
        <!-- Color accent top bar -->
        <div class="h-2 w-full" :style="{ backgroundColor: cardColor }" />

        <div class="p-5">
            <!-- Header: name + actions -->
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-semibold"
                        :style="{
                            backgroundColor: cardColor + '20',
                            color: cardColor,
                        }"
                    >
                        {{ card.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h3 class="leading-tight font-semibold">
                            {{ card.name }}
                        </h3>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ card.card_number }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-1">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-xl"
                        :title="t('loyaltyCards.fullscreen')"
                        @click="emit('fullscreen', card)"
                    >
                        <Maximize2 class="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-xl"
                        @click="emit('edit', card)"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 rounded-xl text-destructive"
                        @click="emit('delete', card)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </Button>
                </div>
            </div>

            <!-- Barcode -->
            <div
                class="mt-4 cursor-pointer rounded-2xl border border-border/40 bg-white p-3"
                @click="emit('fullscreen', card)"
            >
                <BarcodeDisplay
                    :value="card.card_number"
                    :width="1.5"
                    :height="60"
                    :font-size="14"
                />
            </div>

            <!-- Notes -->
            <div
                v-if="card.notes"
                class="mt-3 flex items-start gap-2 rounded-2xl border border-border/40 bg-muted/30 px-3 py-2"
            >
                <StickyNote
                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground"
                />
                <p class="line-clamp-2 text-xs text-muted-foreground">
                    {{ card.notes }}
                </p>
            </div>
        </div>
    </div>
</template>
