<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    eyebrow: string;
    title: string;
    icon: Component;
}>();
</script>

<template>
    <div class="rounded-3xl border border-border/60 bg-card/95 p-5 shadow-sm">
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ eyebrow }}
                </p>
                <h3 class="mt-1 font-semibold">
                    {{ title }}
                </h3>
            </div>
            <div class="rounded-2xl bg-primary/10 p-2 text-primary">
                <component :is="icon" class="h-4 w-4" />
            </div>
        </div>

        <slot />
    </div>
</template>
