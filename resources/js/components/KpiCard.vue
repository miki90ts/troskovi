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
    <div
        class="relative overflow-hidden rounded-3xl border border-border/70 bg-[linear-gradient(180deg,rgba(20,184,166,0.08),rgba(255,255,255,0.95))] p-6 shadow-[0_18px_50px_rgba(15,23,42,0.07)] dark:bg-[linear-gradient(180deg,rgba(20,184,166,0.12),rgba(15,23,42,0.88))]"
    >
        <div
            class="absolute inset-x-0 top-0 h-1 bg-[linear-gradient(90deg,rgba(13,148,136,1),rgba(94,234,212,0.95))]"
        />
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
            <div
                v-if="change !== undefined"
                class="flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium"
                :class="change >= 0 ? 'text-green-600' : 'text-red-600'"
            >
                <ArrowUp v-if="change >= 0" class="h-3 w-3" />
                <ArrowDown v-else class="h-3 w-3" />
                {{ Math.abs(change) }}%
            </div>
        </div>
        <p class="mt-3 text-2xl font-bold tracking-tight text-foreground">
            <span v-if="prefix" class="text-muted-foreground">{{
                prefix
            }}</span>
            {{ value }}
        </p>
        <p v-if="subtitle" class="mt-2 text-xs text-muted-foreground">
            {{ subtitle }}
        </p>
    </div>
</template>
