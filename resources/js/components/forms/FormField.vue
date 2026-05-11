<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

withDefaults(
    defineProps<{
        label?: string;
        fieldId?: string;
        error?: string;
        wrapperClass?: string;
        labelClass?: string;
    }>(),
    {
        label: undefined,
        fieldId: undefined,
        error: undefined,
        wrapperClass: 'grid gap-2',
        labelClass: 'text-xs tracking-[0.18em] text-muted-foreground uppercase',
    },
);

const errorClass = 'border-destructive focus-visible:ring-destructive/20';
</script>

<template>
    <div :class="wrapperClass">
        <slot name="label">
            <Label v-if="label" :for="fieldId" :class="labelClass">
                {{ label }}
            </Label>
        </slot>
        <slot :error-class="error ? errorClass : ''" />
        <slot name="hint" />
        <InputError :message="error" />
    </div>
</template>
