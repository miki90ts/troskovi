<script setup lang="ts">
defineProps<{
    rows?: number;
    columns?: number;
}>();
</script>

<template>
    <div class="w-full animate-pulse space-y-3">
        <div v-for="r in rows ?? 3" :key="r" class="flex gap-4">
            <div
                v-for="c in columns ?? 1"
                :key="c"
                class="h-4 flex-1 rounded bg-muted"
            />
        </div>
    </div>
</template>
