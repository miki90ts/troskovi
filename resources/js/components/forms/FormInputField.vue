<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import type { Component, HTMLAttributes } from 'vue';
import FormField from '@/components/forms/FormField.vue';
import { Input } from '@/components/ui/input';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        label?: string;
        fieldId?: string;
        error?: string;
        component?: Component | string;
        wrapperClass?: string;
        labelClass?: string;
        inputClass?: HTMLAttributes['class'];
    }>(),
    {
        label: undefined,
        fieldId: undefined,
        error: undefined,
        component: Input,
        wrapperClass: 'grid gap-2.5',
        labelClass: 'text-sm font-medium text-foreground/90',
        inputClass:
            'h-12 rounded-2xl border-border/80 bg-background/70 px-4 shadow-none',
    },
);

const attrs = useAttrs();

const controlId = computed(() => {
    if (props.fieldId) {
        return props.fieldId;
    }

    return typeof attrs.id === 'string' ? attrs.id : undefined;
});

const forwardedAttrs = computed(() => {
    const rest = { ...attrs };

    delete rest.class;
    delete rest.id;

    return rest;
});

const attrClass = computed<HTMLAttributes['class']>(
    () => attrs.class as HTMLAttributes['class'],
);
</script>

<template>
    <FormField
        :label="label"
        :field-id="controlId"
        :error="error"
        :wrapper-class="wrapperClass"
        :label-class="labelClass"
    >
        <template #label>
            <slot name="label" />
        </template>
        <template #default="{ errorClass }">
            <component
                :is="component"
                :id="controlId"
                :class="[inputClass, attrClass, errorClass]"
                v-bind="forwardedAttrs"
            />
        </template>
        <template #hint>
            <slot name="hint" />
        </template>
    </FormField>
</template>
