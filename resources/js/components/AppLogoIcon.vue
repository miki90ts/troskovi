<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Background circle -->
        <circle cx="32" cy="32" r="30" fill="currentColor" opacity="0.1" />

        <!-- Chart bars -->
        <rect x="18" y="34" width="4" height="10" rx="2" fill="currentColor" />
        <rect x="26" y="28" width="4" height="16" rx="2" fill="currentColor" />
        <rect x="34" y="22" width="4" height="22" rx="2" fill="currentColor" />

        <!-- Trend line -->
        <path
            d="M16 40 L28 30 L36 34 L46 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        <!-- Currency (stylized RSD / dinar) -->
        <text
            x="32"
            y="20"
            text-anchor="middle"
            font-size="10"
            font-weight="bold"
            fill="currentColor"
            font-family="Arial, sans-serif"
        >
            RSD
        </text>
    </svg>
</template>
