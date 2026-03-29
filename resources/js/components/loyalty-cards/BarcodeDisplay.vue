<script setup lang="ts">
import JsBarcode from 'jsbarcode';
import { onMounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        value: string;
        format?: string;
        width?: number;
        height?: number;
        fontSize?: number;
    }>(),
    {
        format: 'CODE128',
        width: 2,
        height: 80,
        fontSize: 16,
    },
);

const svgRef = ref<SVGSVGElement | null>(null);
const error = ref(false);

function renderBarcode() {
    if (!svgRef.value || !props.value.trim()) {
        return;
    }

    error.value = false;

    try {
        JsBarcode(svgRef.value, props.value, {
            format: props.format,
            width: props.width,
            height: props.height,
            displayValue: true,
            fontSize: props.fontSize,
            margin: 10,
            background: 'transparent',
        });
    } catch {
        error.value = true;
    }
}

onMounted(renderBarcode);
watch(
    () => [props.value, props.format, props.width, props.height],
    renderBarcode,
);
</script>

<template>
    <div class="flex flex-col items-center justify-center">
        <svg v-show="value.trim() && !error" ref="svgRef" />
        <p v-if="!value.trim()" class="py-8 text-sm text-muted-foreground">—</p>
        <p v-else-if="error" class="py-8 text-sm text-destructive">
            {{ format }} format not supported for this value
        </p>
    </div>
</template>
