<script setup lang="ts">
import { Check, ChevronDown, Search, X } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import CategoryBadge from '@/components/categories/CategoryBadge.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { Category } from '@/types/models';

const props = defineProps<{
    modelValue: string;
    categories: Category[];
    placeholder: string;
    allLabel: string;
    triggerClass?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const rootRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const search = ref('');

const selectedIds = computed(() => {
    return props.modelValue
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
});

const selectedIdSet = computed(() => new Set(selectedIds.value));

const selectedCategories = computed(() =>
    props.categories.filter((category) =>
        selectedIdSet.value.has(String(category.id)),
    ),
);

const filteredCategories = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return props.categories;
    }

    return props.categories.filter((category) =>
        category.name.toLowerCase().includes(query),
    );
});

const triggerLabel = computed(() => {
    if (selectedCategories.value.length === 0) {
        return props.placeholder;
    }

    if (selectedCategories.value.length === 1) {
        return selectedCategories.value[0]?.name ?? props.placeholder;
    }

    return `${selectedCategories.value.length} kategorije`;
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

function clearSelection() {
    emit('update:modelValue', '');
    close();
}

function toggleCategory(categoryId: number) {
    const nextSelectedIds = new Set(selectedIds.value);
    const id = String(categoryId);

    if (nextSelectedIds.has(id)) {
        nextSelectedIds.delete(id);
    } else {
        nextSelectedIds.add(id);
    }

    const orderedIds = props.categories
        .map((category) => String(category.id))
        .filter((value) => nextSelectedIds.has(value));

    emit('update:modelValue', orderedIds.join(','));
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
                <template v-if="selectedCategories.length === 0">
                    <span class="truncate text-muted-foreground">
                        {{ placeholder }}
                    </span>
                </template>
                <template v-else-if="selectedCategories.length === 1">
                    <CategoryBadge
                        :category="selectedCategories[0]"
                        compact
                        class="max-w-full"
                    />
                </template>
                <template v-else>
                    <CategoryBadge
                        :category="selectedCategories[0]"
                        compact
                        class="max-w-[calc(100%-3.5rem)]"
                    />
                    <span
                        class="inline-flex shrink-0 items-center rounded-full border border-border/60 bg-muted/60 px-2 py-1 text-xs font-medium text-muted-foreground"
                    >
                        +{{ selectedCategories.length - 1 }}
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
                    placeholder="Pretraži kategorije..."
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
                    {{ allLabel }}
                </button>
                <span class="text-xs text-muted-foreground">
                    {{ triggerLabel }}
                </span>
            </div>

            <div class="mt-3 max-h-72 space-y-1 overflow-y-auto pr-1">
                <button
                    v-for="category in filteredCategories"
                    :key="category.id"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition hover:bg-accent/60"
                    @click="toggleCategory(category.id)"
                >
                    <Checkbox
                        :model-value="selectedIdSet.has(String(category.id))"
                        class="pointer-events-none"
                    >
                        <Check class="h-3.5 w-3.5" />
                    </Checkbox>
                    <CategoryBadge
                        :category="category"
                        compact
                        class="max-w-full"
                    />
                </button>

                <div
                    v-if="filteredCategories.length === 0"
                    class="rounded-xl border border-dashed border-border/60 px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    Nema rezultata za "{{ search }}".
                </div>
            </div>
        </div>
    </div>
</template>
