<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Wallet2 } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';
import { t } from '@/lib/i18n';

const page = usePage();

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-sidebar-border/70 bg-background/75 px-6 backdrop-blur transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <div
                class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-primary/10 px-3 py-1.5 text-xs text-muted-foreground"
            >
                <Wallet2 class="h-3.5 w-3.5 text-primary" />
                {{ t('app.nav.overview') }}
            </div>
        </div>
    </header>
</template>
