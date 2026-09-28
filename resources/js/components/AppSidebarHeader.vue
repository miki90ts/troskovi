<script setup lang="ts">
import { Download, Share, Wallet2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { usePwaInstall } from '@/composables/usePwaInstall';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const showIosInstructions = ref(false);
const { canInstall, requestInstall } = usePwaInstall();

async function installApplication() {
    if ((await requestInstall()) === 'instructions') {
        showIosInstructions.value = true;
    }
}
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

        <div class="flex items-center gap-2 lg:gap-3">
            <Button
                v-if="canInstall"
                type="button"
                variant="outline"
                size="sm"
                class="h-9 rounded-2xl px-2.5 sm:px-3"
                :aria-label="t('pwa.install')"
                @click="installApplication"
            >
                <Download class="h-4 w-4 sm:mr-2" />
                <span class="hidden sm:inline">{{ t('pwa.install') }}</span>
            </Button>
            <div
                class="hidden items-center gap-2 rounded-full border border-border/70 bg-primary/10 px-3 py-1.5 text-xs text-muted-foreground lg:inline-flex"
            >
                <Wallet2 class="h-3.5 w-3.5 text-primary" />
                {{ t('app.nav.overview') }}
            </div>
        </div>
    </header>

    <Dialog v-model:open="showIosInstructions">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ t('pwa.iosTitle') }}</DialogTitle>
                <DialogDescription>
                    {{ t('pwa.iosDescription') }}
                </DialogDescription>
            </DialogHeader>
            <ol class="space-y-3 text-sm text-muted-foreground">
                <li class="flex gap-3">
                    <span class="font-semibold text-primary">1.</span>
                    <span class="flex items-center gap-2">
                        {{ t('pwa.iosStepShare') }}
                        <Share class="h-4 w-4" />
                    </span>
                </li>
                <li class="flex gap-3">
                    <span class="font-semibold text-primary">2.</span>
                    <span>{{ t('pwa.iosStepAdd') }}</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-semibold text-primary">3.</span>
                    <span>{{ t('pwa.iosStepConfirm') }}</span>
                </li>
            </ol>
            <DialogFooter>
                <Button type="button" @click="showIosInstructions = false">
                    {{ t('common.actions.close') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
