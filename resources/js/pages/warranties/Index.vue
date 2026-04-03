<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { toRef } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import WarrantiesHeroSection from '@/components/warranties/WarrantiesHeroSection.vue';
import WarrantiesManagementSection from '@/components/warranties/WarrantiesManagementSection.vue';
import {
    ALL_WARRANTY_STATUSES_VALUE,
    useWarrantiesPage,
} from '@/composables/useWarrantiesPage';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { PaginationMeta } from '@/types/api';
import type { Transaction } from '@/types/models';

const props = defineProps<{
    transactions: { data: Transaction[]; meta: PaginationMeta };
    counts: { active: number; expiring_soon: number; expired: number };
    filters: Record<string, string | undefined>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.warranties'), href: '/warranties' },
];

const { search, statusSelectValue, goToPage } = useWarrantiesPage({
    filters: toRef(props, 'filters'),
});
</script>

<template>
    <Head :title="t('finance.warranties.head')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <WarrantiesHeroSection :counts="counts" />

            <WarrantiesManagementSection
                :transactions="transactions.data"
                :pagination="transactions.meta"
                :search="search"
                :status-filter-value="statusSelectValue"
                :all-statuses-value="ALL_WARRANTY_STATUSES_VALUE"
                @update:search="search = $event"
                @update:status-filter-value="statusSelectValue = $event"
                @page-change="goToPage"
            />
        </div>
    </AppLayout>
</template>
