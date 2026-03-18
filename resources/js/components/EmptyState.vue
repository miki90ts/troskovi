<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    icon?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <div
            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
        >
            <svg
                class="h-8 w-8 text-muted-foreground"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
            </svg>
        </div>
        <h3 class="mb-1 text-lg font-medium">{{ title }}</h3>
        <p
            v-if="description"
            class="mb-4 max-w-sm text-sm text-muted-foreground"
        >
            {{ description }}
        </p>
        <slot />
    </div>
</template>
