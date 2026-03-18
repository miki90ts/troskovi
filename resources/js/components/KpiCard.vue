<script setup lang="ts">
import { ArrowDown, ArrowUp } from 'lucide-vue-next';

defineProps<{
    label: string;
    value: string | number;
    prefix?: string;
    change?: number;
    subtitle?: string;
}>();
</script>

<template>
    <div class="rounded-xl border bg-card p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
            <div
                v-if="change !== undefined"
                class="flex items-center gap-1 text-xs font-medium"
                :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
            >
                <ArrowUp v-if="change >= 0" class="h-3 w-3" />
                <ArrowDown v-else class="h-3 w-3" />
                {{ Math.abs(change) }}%
            </div>
        </div>
        <p class="mt-2 text-2xl font-bold tracking-tight">
            <span v-if="prefix" class="text-muted-foreground">{{
                prefix
            }}</span>
            {{ value }}
        </p>
        <p v-if="subtitle" class="mt-1 text-xs text-muted-foreground">
            {{ subtitle }}
        </p>
    </div>
</template>
