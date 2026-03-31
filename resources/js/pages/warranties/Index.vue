<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Download,
    Eye,
    Search,
    ShieldAlert,
    ShieldCheck,
    ShieldX,
    SlidersHorizontal,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { Transaction } from '@/types/models';
import type { PaginationMeta } from '@/types/api';

const props = defineProps<{
    transactions: { data: Transaction[]; meta: PaginationMeta };
    counts: { active: number; expiring_soon: number; expired: number };
    filters: Record<string, string | undefined>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.warranties'), href: '/warranties' },
];

const ALL_STATUSES_VALUE = '__all__';

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const statusSelectValue = computed({
    get: () => statusFilter.value || ALL_STATUSES_VALUE,
    set: (value: string) => {
        statusFilter.value = value === ALL_STATUSES_VALUE ? '' : value;
    },
});

function applyFilters() {
    const query: Record<string, string> = {};
    if (search.value) query.search = search.value;
    if (statusFilter.value) query.status = statusFilter.value;
    router.get('/warranties', query, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    statusFilter.value = '';
    router.get('/warranties', {}, { preserveState: true });
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

function goToPage(page: number) {
    router.get(
        '/warranties',
        { ...props.filters, page: String(page) },
        { preserveState: true, preserveScroll: true },
    );
}

function daysUntilExpiry(expiresAt: string | null): number | null {
    if (!expiresAt) return null;
    const now = new Date();
    const expires = new Date(expiresAt);
    return Math.ceil(
        (expires.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
    );
}

function warrantyStatusClass(expiresAt: string | null): string {
    const days = daysUntilExpiry(expiresAt);
    if (days === null) return 'text-muted-foreground';
    if (days < 0) return 'text-destructive';
    if (days <= 30) return 'text-orange-500';
    if (days <= 180) return 'text-yellow-500';
    return 'text-emerald-500';
}

function warrantyStatusBadge(expiresAt: string | null): {
    label: string;
    class: string;
} {
    const days = daysUntilExpiry(expiresAt);
    if (days === null || days < 0) {
        return {
            label: t('finance.warranties.statusExpired'),
            class: 'border-destructive/20 bg-destructive/10 text-destructive',
        };
    }
    if (days <= 30) {
        return {
            label: t('finance.warranties.statusExpiringSoon'),
            class: 'border-orange-500/20 bg-orange-500/10 text-orange-600',
        };
    }
    return {
        label: t('finance.warranties.statusActive'),
        class: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-600',
    };
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function formatDaysRemaining(expiresAt: string | null): string {
    const days = daysUntilExpiry(expiresAt);
    if (days === null) return '';
    if (days < 0)
        return t('finance.warranties.daysOverdue', { count: Math.abs(days) });
    return t('finance.warranties.daysRemaining', { count: days });
}

function getReceiptPreviewUrl(receiptUrl: string): string {
    return `${receiptUrl}${receiptUrl.includes('?') ? '&' : '?'}preview=1`;
}
</script>

<template>
    <Head :title="t('finance.warranties.head')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Hero -->
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-2 shadow-sm"
            >
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-6xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.warranties.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-2xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.warranties.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-4 text-muted-foreground"
                            >
                                {{ t('finance.warranties.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t('finance.warranties.activeWarranties')
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-xl font-semibold text-emerald-500"
                                >
                                    {{ counts.active }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.warranties.expiringSoon') }}
                                </p>
                                <p
                                    class="mt-2 text-xl font-semibold text-orange-500"
                                >
                                    {{ counts.expiring_soon }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-2 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.warranties.expired') }}
                                </p>
                                <p
                                    class="mt-2 text-xl font-semibold text-destructive"
                                >
                                    {{ counts.expired }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filters & List -->
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
                            {{ t('finance.warranties.managementTitle') }}
                        </p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight">
                            {{ t('finance.warranties.managementDescription') }}
                        </h2>
                    </div>
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="relative min-w-0 flex-1 sm:w-72">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="search"
                                :placeholder="
                                    t('finance.expenses.searchPlaceholder')
                                "
                                class="h-11 rounded-2xl border-border/60 bg-background pl-9"
                            />
                        </div>
                        <Select
                            v-model="statusSelectValue"
                            @update:model-value="applyFilters"
                        >
                            <SelectTrigger
                                class="h-11 w-44 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="ALL_STATUSES_VALUE">
                                    {{ t('finance.warranties.filterAll') }}
                                </SelectItem>
                                <SelectItem value="active">
                                    {{ t('finance.warranties.filterActive') }}
                                </SelectItem>
                                <SelectItem value="expiring_soon">
                                    {{
                                        t(
                                            'finance.warranties.filterExpiringSoon',
                                        )
                                    }}
                                </SelectItem>
                                <SelectItem value="expired">
                                    {{ t('finance.warranties.filterExpired') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </section>

            <section
                class="overflow-hidden rounded-3xl border border-border/60 bg-card shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.warranties.totalWarranties') }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold">
                            {{
                                t('common.labels.resultsTotal', {
                                    count: transactions.meta.total,
                                })
                            }}
                        </h2>
                    </div>
                </div>

                <div v-if="transactions.data.length === 0">
                    <EmptyState
                        :title="t('finance.warranties.emptyTitle')"
                        :description="t('finance.warranties.emptyDescription')"
                    />
                </div>

                <Table v-else>
                    <TableHeader class="bg-muted/30">
                        <TableRow>
                            <TableHead>{{
                                t('common.labels.description')
                            }}</TableHead>
                            <TableHead>{{
                                t('finance.warranties.purchaseDate')
                            }}</TableHead>
                            <TableHead>{{
                                t('finance.warranties.expiresAt')
                            }}</TableHead>
                            <TableHead>{{
                                t('common.labels.category')
                            }}</TableHead>
                            <TableHead class="text-right">{{
                                t('common.labels.amount')
                            }}</TableHead>
                            <TableHead class="w-20" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="border-border/60"
                        >
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl"
                                        :class="
                                            tx.warranty_is_expired
                                                ? 'bg-destructive/10'
                                                : 'bg-emerald-500/10'
                                        "
                                    >
                                        <ShieldX
                                            v-if="tx.warranty_is_expired"
                                            class="h-4 w-4 text-destructive"
                                        />
                                        <ShieldCheck
                                            v-else
                                            class="h-4 w-4 text-emerald-500"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <span class="block font-medium">{{
                                            tx.description
                                        }}</span>
                                        <span
                                            class="text-xs"
                                            :class="
                                                warrantyStatusClass(
                                                    tx.warranty_expires_at,
                                                )
                                            "
                                        >
                                            {{
                                                formatDaysRemaining(
                                                    tx.warranty_expires_at,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ formatDate(tx.date) }}
                            </TableCell>
                            <TableCell>
                                <div class="space-y-1">
                                    <span class="block text-sm">
                                        {{
                                            tx.warranty_expires_at
                                                ? formatDate(
                                                      tx.warranty_expires_at,
                                                  )
                                                : '—'
                                        }}
                                    </span>
                                    <span
                                        v-if="tx.warranty_expires_at"
                                        class="inline-flex rounded-full border px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            warrantyStatusBadge(
                                                tx.warranty_expires_at,
                                            ).class
                                        "
                                    >
                                        {{
                                            warrantyStatusBadge(
                                                tx.warranty_expires_at,
                                            ).label
                                        }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span
                                    v-if="tx.category"
                                    class="inline-flex items-center gap-1 rounded-full border border-border/60 bg-muted/50 px-3 py-1 text-xs font-medium"
                                >
                                    {{ tx.category.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ t('common.states.uncategorized') }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <CurrencyDisplay
                                    :amount="tx.amount"
                                    class="font-semibold"
                                />
                            </TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <a
                                        v-if="tx.receipt_url"
                                        :href="
                                            getReceiptPreviewUrl(tx.receipt_url)
                                        "
                                        target="_blank"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-2xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        :title="
                                            t('finance.warranties.viewReceipt')
                                        "
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                    </a>
                                    <a
                                        v-if="tx.receipt_url"
                                        :href="tx.receipt_url"
                                        target="_blank"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-2xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        :title="
                                            t(
                                                'finance.warranties.downloadReceipt',
                                            )
                                        "
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                    </a>
                                    <span
                                        v-else
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ t('finance.warranties.noReceipt') }}
                                    </span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div
                    v-if="transactions.meta.last_page > 1"
                    class="flex flex-col gap-3 border-t border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-muted-foreground">
                        {{
                            t('common.labels.shownRange', {
                                from: transactions.meta.from ?? 0,
                                to: transactions.meta.to ?? 0,
                                inTotal: transactions.meta.total,
                            })
                        }}
                    </p>
                    <Pagination
                        :items-per-page="transactions.meta.per_page"
                        :total="transactions.meta.total"
                        :page="transactions.meta.current_page"
                    >
                        <PaginationContent>
                            <PaginationItem
                                :value="transactions.meta.current_page - 1"
                            >
                                <PaginationPrevious
                                    :disabled="
                                        transactions.meta.current_page === 1
                                    "
                                    @click="
                                        goToPage(
                                            transactions.meta.current_page - 1,
                                        )
                                    "
                                />
                            </PaginationItem>
                            <PaginationItem
                                :value="transactions.meta.current_page + 1"
                            >
                                <PaginationNext
                                    :disabled="
                                        transactions.meta.current_page ===
                                        transactions.meta.last_page
                                    "
                                    @click="
                                        goToPage(
                                            transactions.meta.current_page + 1,
                                        )
                                    "
                                />
                            </PaginationItem>
                        </PaginationContent>
                    </Pagination>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
