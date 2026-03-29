<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import BarcodeDisplay from '@/components/loyalty-cards/BarcodeDisplay.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { t } from '@/lib/i18n';
import type { LoyaltyCard } from '@/types/models';

const props = defineProps<{
    card: LoyaltyCard | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const barcodeFormat = ref<'CODE128' | 'EAN13'>('CODE128');

watch(
    () => props.card,
    () => {
        barcodeFormat.value = 'CODE128';
    },
);
</script>

<template>
    <Dialog :open="!!props.card" @update:open="emit('close')">
        <DialogContent
            class="flex max-h-[90dvh] flex-col items-center justify-center rounded-3xl border-0 bg-white p-0 shadow-2xl sm:max-w-lg"
        >
            <DialogTitle class="sr-only">
                {{ props.card?.name }}
            </DialogTitle>

            <div
                v-if="props.card"
                class="flex w-full flex-col items-center px-6 py-8"
            >
                <!-- Card color accent -->
                <div
                    class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold text-white shadow-lg"
                    :style="{ backgroundColor: props.card.color ?? '#14b8a6' }"
                >
                    {{ props.card.name.charAt(0).toUpperCase() }}
                </div>

                <!-- Card name -->
                <h2 class="mb-1 text-xl font-semibold text-gray-900">
                    {{ props.card.name }}
                </h2>

                <p class="mb-6 text-sm text-gray-500">
                    {{ t('loyaltyCards.scanAtStore') }}
                </p>

                <!-- Format toggle -->
                <div class="mb-4 flex gap-2">
                    <Button
                        size="sm"
                        :variant="
                            barcodeFormat === 'CODE128' ? 'default' : 'outline'
                        "
                        class="rounded-xl text-xs"
                        @click="barcodeFormat = 'CODE128'"
                    >
                        CODE 128
                    </Button>
                    <Button
                        size="sm"
                        :variant="
                            barcodeFormat === 'EAN13' ? 'default' : 'outline'
                        "
                        class="rounded-xl text-xs"
                        @click="barcodeFormat = 'EAN13'"
                    >
                        EAN-13
                    </Button>
                </div>

                <!-- Large barcode for scanning -->
                <div
                    class="w-full rounded-2xl border border-gray-100 bg-white p-4"
                >
                    <BarcodeDisplay
                        :value="props.card.card_number"
                        :format="barcodeFormat"
                        :width="2.5"
                        :height="120"
                        :font-size="18"
                    />
                </div>

                <!-- Notes -->
                <p
                    v-if="props.card.notes"
                    class="mt-4 max-w-sm text-center text-xs text-gray-400"
                >
                    {{ props.card.notes }}
                </p>

                <!-- Close button -->
                <Button
                    variant="outline"
                    class="mt-6 rounded-2xl"
                    @click="emit('close')"
                >
                    <X class="mr-2 h-4 w-4" />
                    {{ t('common.actions.close') }}
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
