<script setup lang="ts">
import { Check, ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { Category } from '@/types/models';

const props = defineProps<{
    modelValue: string;
    categories: Category[];
    placeholder: string;
    searchPlaceholder: string;
    emptyResultsLabel: string;
    clearLabel: string;
    triggerClass?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const rootRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const search = ref('');

const selectedCategory = computed(() => {
    return (
        props.categories.find(
            (category) => String(category.id) === props.modelValue,
        ) ?? null
    );
});

const filteredCategories = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return props.categories;
    }

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(query),
    );
});

watch(isOpen, (open) => {
    if (!open) {
        search.value = '';
        return;
    }

    void nextTick(() => {
        searchInputRef.value?.focus();
    });
});

function toggleOpen() {
    isOpen.value = !isOpen.value;
}

function close() {
    isOpen.value = false;
}

function selectCategory(categoryId: number) {
    emit('update:modelValue', String(categoryId));
    close();
}

function clearSelection() {
    emit('update:modelValue', '');
    close();
}

function handleDocumentPointerDown(event: PointerEvent) {
    if (!isOpen.value) {
        return;
    }

    const target = event.target;

    if (!(target instanceof Node)) {
        return;
    }

    if (!rootRef.value?.contains(target)) {
        close();
    }
}

document.addEventListener('pointerdown', handleDocumentPointerDown);

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handleDocumentPointerDown);
});
</script>

<template>
    <div ref="rootRef" class="relative">
        <Button
            type="button"
            variant="outline"
            :class="
                cn(
                    'h-11 w-full justify-between rounded-2xl border-border/60 bg-background px-3 text-left font-normal',
                    triggerClass,
                )
            "
            @click="toggleOpen"
        >
            <span
                class="flex min-w-0 flex-1 items-center gap-2 overflow-hidden"
            >
                <template v-if="selectedCategory">
                    <CategoryBadge
                        :category="selectedCategory"
                        compact
                        class="max-w-full"
                    />
                </template>
                <template v-else>
                    <span class="truncate text-muted-foreground">
                        {{ placeholder }}
                    </span>
                </template>
            </span>
            <ChevronDown class="ml-2 h-4 w-4 shrink-0 text-muted-foreground" />
        </Button>

        <div
            v-if="isOpen"
            class="absolute top-full z-50 mt-2 w-full rounded-2xl border border-border/70 bg-popover p-3 shadow-xl"
        >
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    ref="searchInputRef"
                    v-model="search"
                    type="text"
                    class="h-10 rounded-xl border-border/60 bg-background pr-9 pl-9"
                    :placeholder="searchPlaceholder"
                />
                <button
                    v-if="search"
                    type="button"
                    class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground transition hover:text-foreground"
                    @click="search = ''"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2">
                <button
                    type="button"
                    class="text-sm font-medium text-muted-foreground transition hover:text-foreground"
                    @click="clearSelection"
                >
                    {{ clearLabel }}
                </button>
                <span class="text-xs text-muted-foreground">
                    {{ selectedCategory?.name ?? placeholder }}
                </span>
            </div>

            <div class="mt-3 max-h-72 space-y-1 overflow-y-auto pr-1">
                <button
                    v-for="category in filteredCategories"
                    :key="category.id"
                    type="button"
                    class="flex w-full items-center justify-between gap-3 rounded-xl px-2 py-2 text-left transition hover:bg-accent/60"
                    @click="selectCategory(category.id)"
                >
                    <CategoryBadge
                        :category="category"
                        compact
                        class="max-w-[calc(100%-1.5rem)]"
                    />
                    <Check
                        v-if="String(category.id) === modelValue"
                        class="h-4 w-4 shrink-0 text-primary"
                    />
                </button>

                <div
                    v-if="filteredCategories.length === 0"
                    class="rounded-xl border border-dashed border-border/60 px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    {{ emptyResultsLabel }}
                </div>
            </div>
        </div>
    </div>
</template>
