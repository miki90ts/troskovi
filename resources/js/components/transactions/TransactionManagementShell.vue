<script setup lang="ts">
defineProps<{
    eyebrow: string;
    title: string;
}>();
</script>

<template>
    <section
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <p
                    class="text-xs font-medium tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ eyebrow }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ title }}
                </h2>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <slot name="header-actions" />
            </div>
        </div>

        <slot name="filters" />

        <div class="mt-5">
            <slot />
        </div>
    </section>
</template>
