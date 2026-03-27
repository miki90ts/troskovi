<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { t } from '@/lib/i18n';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: t('settings.nav.profile'),
        href: editProfile(),
    },
    {
        title: t('settings.nav.security'),
        href: editSecurity(),
    },
    {
        title: t('settings.nav.budgets'),
        href: '/settings/budgets',
    },
    {
        title: t('settings.nav.appearance'),
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <div
            class="mb-8 overflow-hidden rounded-4xl border border-border/70 bg-[linear-gradient(135deg,rgba(13,148,136,0.12),rgba(255,255,255,0.96))] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.07)] dark:bg-[linear-gradient(135deg,rgba(13,148,136,0.18),rgba(15,23,42,0.86))]"
        >
            <Heading
                :title="t('settings.common.settings')"
                :description="t('settings.nav.description')"
            />
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-2 space-x-0"
                    :aria-label="t('settings.common.settings')"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'h-11 w-full justify-start rounded-2xl border border-transparent px-4',
                            {
                                'border-primary/20 bg-primary/10 text-primary hover:bg-primary/12':
                                    isCurrentOrParentUrl(item.href),
                                'hover:bg-muted/60': !isCurrentOrParentUrl(
                                    item.href,
                                ),
                            },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-4xl">
                <section class="max-w-4xl space-y-8">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
