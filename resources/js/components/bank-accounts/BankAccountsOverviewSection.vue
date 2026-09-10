<script setup lang="ts">
import { Archive, Plus, RotateCcw } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { computed, ref, watch } from 'vue';
import BankAccountCard from '@/components/bank-accounts/BankAccountCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { t } from '@/lib/i18n';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    activeAccounts: BankAccount[];
    archivedAccounts: BankAccount[];
    connectedBanks: number;
}>();

const perPageOptions = ['15', '30', '50', '100'] as const;
const perPage = ref('15');
const activePage = ref(1);
const archivedPage = ref(1);
const itemsPerPage = computed(() => Number(perPage.value));
const activeLastPage = computed(() =>
    Math.max(1, Math.ceil(props.activeAccounts.length / itemsPerPage.value)),
);
const archivedLastPage = computed(() =>
    Math.max(1, Math.ceil(props.archivedAccounts.length / itemsPerPage.value)),
);
const paginatedActiveAccounts = computed(() => {
    const start = (activePage.value - 1) * itemsPerPage.value;

    return props.activeAccounts.slice(start, start + itemsPerPage.value);
});
const paginatedArchivedAccounts = computed(() => {
    const start = (archivedPage.value - 1) * itemsPerPage.value;

    return props.archivedAccounts.slice(start, start + itemsPerPage.value);
});

function handlePerPageChange(value: AcceptableValue) {
    if (value !== null && value !== undefined) {
        perPage.value = String(value);
    }
}

watch(perPage, () => {
    activePage.value = 1;
    archivedPage.value = 1;
});

watch(
    () => [props.activeAccounts.length, props.archivedAccounts.length],
    () => {
        activePage.value = Math.min(activePage.value, activeLastPage.value);
        archivedPage.value = Math.min(
            archivedPage.value,
            archivedLastPage.value,
        );
    },
);

const emit = defineEmits<{
    create: [];
    edit: [account: BankAccount];
    archive: [account: BankAccount];
    restore: [account: BankAccount];
}>();
</script>

<template>
    <div v-if="activeAccounts.length === 0 && archivedAccounts.length === 0">
        <EmptyState
            :title="t('finance.bankAccounts.emptyTitle')"
            :description="t('finance.bankAccounts.emptyDescription')"
        >
            <Button class="rounded-2xl px-5" @click="emit('create')">
                <Plus class="mr-2 h-4 w-4" />
                {{ t('finance.bankAccounts.add') }}
            </Button>
        </EmptyState>
    </div>

    <section
        v-else
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <div
            class="mb-5 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="space-y-3">
                <p
                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                >
                    {{ t('finance.bankAccounts.overviewTitle') }}
                </p>
                <h2 class="mt-1 text-lg font-semibold tracking-tight">
                    {{ t('finance.bankAccounts.overviewDescription') }}
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-muted-foreground">{{
                        t('common.labels.rowsPerPage')
                    }}</span>
                    <Select
                        :model-value="perPage"
                        @update:model-value="handlePerPageChange"
                    >
                        <SelectTrigger
                            class="h-10 w-23 rounded-2xl border-border/60 bg-background"
                            ><SelectValue
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in perPageOptions"
                                :key="option"
                                :value="option"
                                >{{ option }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>{{
                    t('finance.bankAccounts.activeCount', {
                        count: activeAccounts.length,
                    })
                }}</span>
                <span class="hidden h-1 w-1 rounded-full bg-border sm:block" />
                <span>{{
                    t('finance.bankAccounts.banksCount', {
                        count: connectedBanks,
                    })
                }}</span>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <BankAccountCard
                v-for="account in paginatedActiveAccounts"
                :key="account.id"
                :account="account"
                @edit="emit('edit', $event)"
                @archive="emit('archive', $event)"
            />
        </div>
        <div
            v-if="activeAccounts.length > 0"
            class="mt-5 flex flex-col gap-3 border-t border-border/60 pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                {{
                    t('common.labels.shownRange', {
                        from: (activePage - 1) * itemsPerPage + 1,
                        to: Math.min(
                            activePage * itemsPerPage,
                            activeAccounts.length,
                        ),
                        inTotal: activeAccounts.length,
                    })
                }}
            </p>
            <Pagination
                v-if="activeLastPage > 1"
                :items-per-page="itemsPerPage"
                :total="activeAccounts.length"
                :page="activePage"
            >
                <PaginationContent>
                    <PaginationItem :value="activePage - 1">
                        <PaginationPrevious
                            :disabled="activePage === 1"
                            @click="activePage = Math.max(1, activePage - 1)"
                        />
                    </PaginationItem>
                    <PaginationItem :value="activePage + 1">
                        <PaginationNext
                            :disabled="activePage === activeLastPage"
                            @click="
                                activePage = Math.min(
                                    activeLastPage,
                                    activePage + 1,
                                )
                            "
                        />
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    </section>

    <section
        v-if="archivedAccounts.length > 0"
        class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
    >
        <h2 class="mb-3 text-lg font-semibold text-muted-foreground">
            {{ t('finance.bankAccounts.archived') }}
        </h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="account in paginatedArchivedAccounts"
                :key="account.id"
                class="rounded-3xl border border-dashed border-border/70 bg-background/60 p-6 opacity-70"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <Archive class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <p class="font-medium">
                                {{ account.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ account.bank_name }}
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="rounded-2xl"
                        @click="emit('restore', account)"
                    >
                        <RotateCcw class="mr-1 h-3 w-3" />
                        {{ t('finance.bankAccounts.restore') }}
                    </Button>
                </div>
            </div>
        </div>
        <div
            class="mt-5 flex flex-col gap-3 border-t border-border/60 pt-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                {{
                    t('common.labels.shownRange', {
                        from: (archivedPage - 1) * itemsPerPage + 1,
                        to: Math.min(
                            archivedPage * itemsPerPage,
                            archivedAccounts.length,
                        ),
                        inTotal: archivedAccounts.length,
                    })
                }}
            </p>
            <Pagination
                v-if="archivedLastPage > 1"
                :items-per-page="itemsPerPage"
                :total="archivedAccounts.length"
                :page="archivedPage"
            >
                <PaginationContent>
                    <PaginationItem :value="archivedPage - 1">
                        <PaginationPrevious
                            :disabled="archivedPage === 1"
                            @click="
                                archivedPage = Math.max(1, archivedPage - 1)
                            "
                        />
                    </PaginationItem>
                    <PaginationItem :value="archivedPage + 1">
                        <PaginationNext
                            :disabled="archivedPage === archivedLastPage"
                            @click="
                                archivedPage = Math.min(
                                    archivedLastPage,
                                    archivedPage + 1,
                                )
                            "
                        />
                    </PaginationItem>
                </PaginationContent>
            </Pagination>
        </div>
    </section>
</template>
