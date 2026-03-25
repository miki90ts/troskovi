<script setup lang="ts">
const props = defineProps<{
    amount: number;
    currency?: string;
    colored?: boolean;
}>();

const formatted = new Intl.NumberFormat('sr-RS', {
    style: 'currency',
    currency: props.currency ?? 'RSD',
    minimumFractionDigits: 2,
}).format(props.amount);
</script>

<template>
    <span
        :class="{
            'text-green-600 dark:text-green-400': colored && amount > 0,
            'text-red-600 dark:text-red-400': colored && amount < 0,
        }"
    >
        {{ formatted }}
    </span>
</template>
