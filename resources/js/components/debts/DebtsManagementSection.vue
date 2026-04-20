<script setup lang="ts">
import { HandCoins, Plus, Search } from 'lucide-vue-next';
import DebtCard from '@/components/debts/DebtCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { DebtTab } from '@/composables/useDebtsPage';
import { t } from '@/lib/i18n';
import { formatCurrency } from '@/lib/i18n';
import type { Debt } from '@/types/models';

defineProps<{
    activeTab: DebtTab;
    filteredDebts: Debt[];
    iOweDebts: Debt[];
    owedToMeDebts: Debt[];
    activeTabTotal: number;
    searchQuery: string;
    statusFilter: 'all' | 'active' | 'settled' | 'overdue';
}>();

const emit = defineEmits<{
    'update:activeTab': [value: DebtTab];
    'update:searchQuery': [value: string];
    'update:statusFilter': [value: 'all' | 'active' | 'settled' | 'overdue'];
    create: [];
    edit: [debt: Debt];
    delete: [debt: Debt];
    settle: [debt: Debt];
}>();
</script>

<template>
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
                    {{ t('debts.managementTitle') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight">
                    {{ t('debts.managementDescription') }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        :model-value="searchQuery"
                        :placeholder="t('debts.searchPlaceholder')"
                        class="h-11 w-64 rounded-2xl border-border/60 bg-background pl-9"
                        @update:model-value="
                            emit('update:searchQuery', $event as string)
                        "
                    />
                </div>
                <Button class="h-11 rounded-2xl px-5" @click="emit('create')">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ t('debts.add') }}
                </Button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mt-5 flex gap-2 border-b border-border/40 pb-3">
            <Button
                variant="ghost"
                size="sm"
                class="rounded-xl px-4"
                :class="
                    activeTab === 'i_owe'
                        ? 'bg-orange-500/10 text-orange-700'
                        : 'text-muted-foreground'
                "
                @click="emit('update:activeTab', 'i_owe')"
            >
                {{
                    t('debts.iOweTab', {
                        count: iOweDebts.length,
                    })
                }}
            </Button>
            <Button
                variant="ghost"
                size="sm"
                class="rounded-xl px-4"
                :class="
                    activeTab === 'owed_to_me'
                        ? 'bg-emerald-500/10 text-emerald-700'
                        : 'text-muted-foreground'
                "
                @click="emit('update:activeTab', 'owed_to_me')"
            >
                {{
                    t('debts.owedToMeTab', {
                        count: owedToMeDebts.length,
                    })
                }}
            </Button>

            <div class="ml-auto flex items-center gap-2">
                <select
                    :value="statusFilter"
                    class="h-8 rounded-xl border border-border/60 bg-background px-2 text-xs"
                    @change="
                        emit(
                            'update:statusFilter',
                            ($event.target as HTMLSelectElement).value as any,
                        )
                    "
                >
                    <option value="all">
                        {{ t('debts.filterAll') }}
                    </option>
                    <option value="active">
                        {{ t('debts.statusActive') }}
                    </option>
                    <option value="settled">
                        {{ t('debts.statusSettled') }}
                    </option>
                    <option value="overdue">
                        {{ t('debts.statusOverdue') }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Active tab total -->
        <div
            v-if="filteredDebts.length > 0"
            class="mt-3 flex items-center justify-between text-sm"
        >
            <span class="text-muted-foreground">
                {{
                    t('debts.showingCount', {
                        count: filteredDebts.length,
                    })
                }}
            </span>
            <span class="font-medium">
                {{ t('debts.totalRemaining') }}:
                {{ formatCurrency(activeTabTotal) }}
            </span>
        </div>

        <!-- Cards grid -->
        <div
            v-if="filteredDebts.length > 0"
            class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
            <DebtCard
                v-for="debt in filteredDebts"
                :key="debt.id"
                :debt="debt"
                @edit="emit('edit', $event)"
                @delete="emit('delete', $event)"
                @settle="emit('settle', $event)"
            />
        </div>

        <!-- Empty state -->
        <EmptyState
            v-else
            :title="
                activeTab === 'i_owe'
                    ? t('debts.emptyIOweTitle')
                    : t('debts.emptyOwedToMeTitle')
            "
            :description="
                activeTab === 'i_owe'
                    ? t('debts.emptyIOweDescription')
                    : t('debts.emptyOwedToMeDescription')
            "
        >
            <Button class="mt-2 rounded-2xl px-5" @click="emit('create')">
                <HandCoins class="mr-2 h-4 w-4" />
                {{ t('debts.add') }}
            </Button>
        </EmptyState>
    </section>
</template>
