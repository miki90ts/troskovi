<script setup lang="ts">
import { CreditCard, Plus, Search } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import BarcodeFullscreenDialog from '@/components/loyalty-cards/BarcodeFullscreenDialog.vue';
import LoyaltyCardFormDialog from '@/components/loyalty-cards/LoyaltyCardFormDialog.vue';
import LoyaltyCardItem from '@/components/loyalty-cards/LoyaltyCardItem.vue';
import LoyaltyCardsHeroSection from '@/components/loyalty-cards/LoyaltyCardsHeroSection.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLoyaltyCardsPage } from '@/composables/useLoyaltyCardsPage';
import AppLayout from '@/layouts/AppLayout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { LoyaltyCard } from '@/types/models';

const props = defineProps<{
    cards: { data: LoyaltyCard[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.loyaltyCards'), href: '/loyalty-cards' },
];

const {
    cards,
    showForm,
    editingCard,
    formSubmitting,
    deleteTarget,
    fullscreenCard,
    searchQuery,
    cardForm,
    filteredCards,
    colorPresets,
    openCreate,
    openEdit,
    openFullscreen,
    applyPresetColor,
    submitForm,
    handleDelete,
} = useLoyaltyCardsPage(props.cards.data);
</script>

<template>
    <Head :title="t('loyaltyCards.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <LoyaltyCardsHeroSection
                :total-cards="cards.length"
                @create="openCreate"
            />

            <!-- Management section -->
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
                            {{ t('loyaltyCards.managementTitle') }}
                        </p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight">
                            {{ t('loyaltyCards.managementDescription') }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                :placeholder="
                                    t('loyaltyCards.searchPlaceholder')
                                "
                                class="h-11 w-64 rounded-2xl border-border/60 bg-background pl-9"
                            />
                        </div>
                        <Button
                            class="h-11 rounded-2xl px-5"
                            @click="openCreate"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            {{ t('loyaltyCards.addCard') }}
                        </Button>
                    </div>
                </div>

                <!-- Cards grid -->
                <div
                    v-if="filteredCards.length > 0"
                    class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <LoyaltyCardItem
                        v-for="card in filteredCards"
                        :key="card.id"
                        :card="card"
                        @edit="openEdit"
                        @delete="deleteTarget = $event"
                        @fullscreen="openFullscreen"
                    />
                </div>

                <!-- Empty state -->
                <EmptyState
                    v-else-if="cards.length === 0"
                    :title="t('loyaltyCards.emptyTitle')"
                    :description="t('loyaltyCards.emptyDescription')"
                >
                    <Button class="mt-2 rounded-2xl px-5" @click="openCreate">
                        <CreditCard class="mr-2 h-4 w-4" />
                        {{ t('loyaltyCards.addCard') }}
                    </Button>
                </EmptyState>

                <!-- No search results -->
                <EmptyState
                    v-else
                    :title="t('loyaltyCards.noResults')"
                    :description="t('loyaltyCards.noResultsDescription')"
                />
            </section>
        </div>

        <LoyaltyCardFormDialog
            :open="showForm"
            :editing-card="editingCard"
            :form-submitting="formSubmitting"
            :color-presets="colorPresets"
            :form="cardForm"
            @update:open="(value) => (showForm = value)"
            @update:form="(value) => (cardForm = value)"
            @submit="submitForm"
            @close="showForm = false"
            @apply-preset-color="applyPresetColor"
        />

        <BarcodeFullscreenDialog
            :card="fullscreenCard"
            @close="fullscreenCard = null"
        />

        <ConfirmDialog
            :open="!!deleteTarget"
            :title="t('loyaltyCards.deleteTitle')"
            :description="t('loyaltyCards.deleteDescription')"
            :confirm-text="t('common.actions.delete')"
            :cancel-text="t('common.actions.cancel')"
            destructive
            @confirm="handleDelete"
            @cancel="deleteTarget = null"
        />
    </AppLayout>
</template>
