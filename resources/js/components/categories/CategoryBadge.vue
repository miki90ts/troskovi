<script setup lang="ts">
import { computed } from 'vue';
import {
    getCategoryIconComponent,
    getCategoryIconText,
} from '@/lib/categoryIcons';
import type { CategoryCompact } from '@/types/models';

const props = defineProps<{
    category: CategoryCompact;
    compact?: boolean;
}>();

const iconComponent = computed(() =>
    getCategoryIconComponent(props.category.icon),
);
const iconText = computed(() =>
    getCategoryIconText(props.category.icon, props.category.name),
);
const badgeStyle = computed(() => {
    if (!props.category.color) {
        return undefined;
    }

    return {
        borderColor: props.category.color,
        backgroundColor: `${props.category.color}14`,
    };
});
const iconStyle = computed(() => ({
    backgroundColor: `${props.category.color ?? '#6b7280'}20`,
    color: props.category.color ?? '#6b7280',
}));
</script>

<template>
    <span
        class="inline-flex max-w-full items-center rounded-full border border-border/60 bg-muted/50 text-xs font-medium text-foreground"
        :class="compact ? 'gap-1.5 px-2.5 py-1' : 'gap-2 px-3 py-1.5'"
        :style="badgeStyle"
    >
        <span
            class="flex shrink-0 items-center justify-center rounded-full text-[10px] font-semibold"
            :class="compact ? 'h-5 w-5' : 'h-6 w-6'"
            :style="iconStyle"
        >
            <component
                :is="iconComponent"
                v-if="iconComponent"
                :class="compact ? 'h-3 w-3' : 'h-3.5 w-3.5'"
            />
            <span v-else>{{ iconText }}</span>
        </span>
        <span class="truncate">{{ category.name }}</span>
    </span>
</template>
