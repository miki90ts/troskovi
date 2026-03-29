<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Landmark, MoreHorizontal } from 'lucide-vue-next';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { t } from '@/lib/i18n';
import type { BankAccount } from '@/types/models';

defineProps<{
    account: BankAccount;
}>();

const emit = defineEmits<{
    edit: [account: BankAccount];
    archive: [account: BankAccount];
}>();
</script>

<template>
    <Link
        :href="`/bank-accounts/${account.id}`"
        class="group relative rounded-3xl border border-border/60 bg-background/80 p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
        <div class="absolute top-4 right-4" @click.prevent.stop>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9 rounded-2xl"
                    >
                        <MoreHorizontal class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="emit('edit', account)">
                        {{ t('finance.bankAccounts.editMenu') }}
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        class="text-destructive"
                        @click="emit('archive', account)"
                    >
                        {{ t('finance.bankAccounts.archiveMenu') }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
        <div class="flex items-center gap-3">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm"
                :style="{
                    backgroundColor: (account.color ?? '#3b82f6') + '20',
                }"
            >
                <Landmark
                    class="h-5 w-5"
                    :style="{ color: account.color ?? '#3b82f6' }"
                />
            </div>
            <div>
                <p class="font-medium">{{ account.name }}</p>
                <p class="text-xs text-muted-foreground">
                    {{ account.bank_name }}
                </p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-xs text-muted-foreground">
                {{ t('finance.bankAccounts.currentBalance') }}
            </p>
            <CurrencyDisplay
                :amount="account.current_balance"
                :currency="account.currency"
                colored
                class="text-xl font-bold"
            />
        </div>
        <p class="mt-2 text-xs text-muted-foreground">
            {{ account.masked_account_number }} &middot;
            {{ account.currency }}
        </p>
    </Link>
</template>
