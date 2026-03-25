<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { t } from '@/lib/i18n';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('settings.appearance.head'),
        href: edit(),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('settings.appearance.head')" />

        <h1 class="sr-only">{{ t('settings.appearance.head') }}</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <Heading
                    variant="small"
                    :title="t('settings.appearance.title')"
                    :description="t('settings.appearance.description')"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
