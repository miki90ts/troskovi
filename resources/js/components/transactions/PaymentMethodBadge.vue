<script setup lang="ts">
import { computed } from 'vue';
import { Landmark, Wallet } from 'lucide-vue-next';
import { t } from '@/lib/i18n';

const props = defineProps<{
    paymentMethod: 'cash' | 'bank_account';
    compact?: boolean;
}>();

const isBankAccount = computed(() => props.paymentMethod === 'bank_account');

const label = computed(() => {
    return isBankAccount.value
        ? t('common.paymentMethods.bankAccount')
        : t('common.paymentMethods.cash');
});

const iconComponent = computed(() => {
    return isBankAccount.value ? Landmark : Wallet;
});

const badgeToneClass = computed(() => {
    return isBankAccount.value
        ? 'border-sky-200/70 bg-sky-500/10 text-sky-950 dark:border-sky-700/60 dark:bg-sky-500/15 dark:text-sky-100'
        : 'border-emerald-200/70 bg-emerald-500/10 text-emerald-950 dark:border-emerald-700/60 dark:bg-emerald-500/15 dark:text-emerald-100';
});

const iconToneClass = computed(() => {
    return isBankAccount.value
        ? 'bg-sky-500/15 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200'
        : 'bg-emerald-500/15 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200';
});
</script>

<template>
    <span
        class="inline-flex max-w-full items-center rounded-full border text-xs font-medium"
        :class="[
            compact ? 'gap-1.5 px-2.5 py-1' : 'gap-2 px-3 py-1.5',
            badgeToneClass,
        ]"
    >
        <span
            class="flex shrink-0 items-center justify-center rounded-full"
            :class="[compact ? 'h-5 w-5' : 'h-6 w-6', iconToneClass]"
        >
            <component
                :is="iconComponent"
                :class="compact ? 'h-3 w-3' : 'h-3.5 w-3.5'"
            />
        </span>
        <span class="truncate">{{ label }}</span>
    </span>
</template>
