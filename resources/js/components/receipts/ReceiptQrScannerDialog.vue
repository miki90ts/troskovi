<script setup lang="ts">
import type { BrowserQRCodeReader, IScannerControls } from '@zxing/browser';
import { Camera, ExternalLink, ImageUp, Link2, QrCode } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
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
import { t } from '@/lib/i18n';
import { stopMediaStream } from '@/lib/media';
import { normalizeFiscalVerificationUrl } from '@/lib/receiptQr';

const props = defineProps<{
    open: boolean;
    currentUrl?: string | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    scanned: [url: string];
}>();

const video = ref<HTMLVideoElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const manualUrl = ref('');
const message = ref('');
const cameraStarting = ref(false);
const scanningImage = ref(false);
let reader: BrowserQRCodeReader | null = null;
let controls: IScannerControls | null = null;
let cameraSession = 0;

function stopCamera() {
    cameraSession += 1;
    controls?.stop();
    controls = null;

    const stream = video.value?.srcObject;

    if (stream && typeof (stream as MediaStream).getTracks === 'function') {
        stopMediaStream(stream as MediaStream);
    }

    if (video.value) {
        video.value.srcObject = null;
    }
}

async function getReader(): Promise<BrowserQRCodeReader> {
    if (!reader) {
        const { BrowserQRCodeReader } = await import('@zxing/browser');
        reader = new BrowserQRCodeReader(undefined, {
            delayBetweenScanAttempts: 250,
            delayBetweenScanSuccess: 750,
        });
    }

    return reader;
}

function acceptCandidate(candidate: string) {
    const normalized = normalizeFiscalVerificationUrl(candidate);

    if (!normalized) {
        message.value = t('components.transactionForm.qrInvalid');

        return;
    }

    stopCamera();
    manualUrl.value = normalized;
    message.value = t('components.transactionForm.qrRecognized');
    window.open(normalized, '_blank', 'noopener,noreferrer');
    emit('scanned', normalized);
}

async function startCamera() {
    if (!video.value || cameraStarting.value) {
        return;
    }

    stopCamera();
    const session = ++cameraSession;
    cameraStarting.value = true;
    message.value = '';

    try {
        const qrReader = await getReader();
        const nextControls = await qrReader.decodeFromConstraints(
            {
                audio: false,
                video: { facingMode: { ideal: 'environment' } },
            },
            video.value,
            (result) => {
                if (result) {
                    acceptCandidate(result.getText());
                }
            },
        );

        if (session !== cameraSession) {
            nextControls.stop();
        } else {
            controls = nextControls;
        }
    } catch {
        message.value = t('components.transactionForm.qrCameraUnavailable');
        stopCamera();
    } finally {
        cameraStarting.value = false;
    }
}

async function scanImage(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    scanningImage.value = true;
    message.value = '';
    const objectUrl = URL.createObjectURL(file);

    try {
        const qrReader = await getReader();
        const result = await qrReader.decodeFromImageUrl(objectUrl);
        acceptCandidate(result.getText());
    } catch {
        message.value = t('components.transactionForm.qrNotFound');
    } finally {
        URL.revokeObjectURL(objectUrl);
        input.value = '';
        scanningImage.value = false;
    }
}

function applyManualUrl() {
    acceptCandidate(manualUrl.value);
}

function close() {
    stopCamera();
    emit('update:open', false);
}

watch(
    () => props.open,
    async (open) => {
        if (!open) {
            stopCamera();

            return;
        }

        manualUrl.value = props.currentUrl ?? '';
        message.value = '';
        await nextTick();
        await startCamera();
    },
);

onMounted(() => window.addEventListener('pagehide', stopCamera));
onBeforeUnmount(() => {
    window.removeEventListener('pagehide', stopCamera);
    stopCamera();
});
</script>

<template>
    <Dialog :open="open" @update:open="(value) => !value && close()">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <QrCode class="h-5 w-5 text-primary" />
                    {{ t('components.transactionForm.qrTitle') }}
                </DialogTitle>
                <DialogDescription>
                    {{ t('components.transactionForm.qrDescription') }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl bg-black">
                    <video
                        ref="video"
                        muted
                        playsinline
                        class="aspect-video w-full object-cover"
                    />
                </div>

                <p
                    v-if="message"
                    class="rounded-2xl border border-border/60 bg-muted/30 px-3 py-2 text-sm text-muted-foreground"
                >
                    {{ message }}
                </p>

                <div class="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="cameraStarting"
                        @click="startCamera"
                    >
                        <Camera class="mr-2 h-4 w-4" />
                        {{ t('components.transactionForm.qrRetryCamera') }}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="scanningImage"
                        @click="fileInput?.click()"
                    >
                        <ImageUp class="mr-2 h-4 w-4" />
                        {{ t('components.transactionForm.qrChooseImage') }}
                    </Button>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="scanImage"
                    />
                </div>

                <div class="space-y-2 border-t border-border/60 pt-4">
                    <label class="text-sm font-medium" for="receipt_qr_url">
                        {{ t('components.transactionForm.qrManual') }}
                    </label>
                    <div class="flex gap-2">
                        <Input
                            id="receipt_qr_url"
                            v-model="manualUrl"
                            type="url"
                            autocomplete="off"
                            :placeholder="
                                t('components.transactionForm.qrPlaceholder')
                            "
                        />
                        <Button type="button" @click="applyManualUrl">
                            <Link2 class="mr-2 h-4 w-4" />
                            {{ t('common.actions.confirm') }}
                        </Button>
                    </div>
                </div>

                <a
                    v-if="normalizeFiscalVerificationUrl(manualUrl)"
                    :href="
                        normalizeFiscalVerificationUrl(manualUrl) ?? undefined
                    "
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline"
                >
                    <ExternalLink class="h-4 w-4" />
                    {{ t('components.transactionForm.qrOpenTax') }}
                </a>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" @click="close">
                    {{ t('common.actions.close') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
