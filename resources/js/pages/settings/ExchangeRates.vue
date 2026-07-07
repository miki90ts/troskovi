<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useExchangeRates } from '@/composables/useExchangeRates';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { CurrencySummary, ExchangeRate } from '@/types/models';

const props = defineProps<{
    currencies: CurrencySummary[];
    exchangeRates: { data: ExchangeRate[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('settings.exchangeRates.head'),
        href: '/settings/exchange-rates',
    },
];

const { saveRate, deleteRate, loading } = useExchangeRates();
const { success, error } = useToast();
const rates = ref<ExchangeRate[]>([...props.exchangeRates.data]);
const editingKey = ref<string | null>(null);
const form = ref({
    currency_id: props.currencies[0] ? String(props.currencies[0].id) : '',
    date: new Date().toISOString().slice(0, 10),
    rate: '',
});

const sortedRates = computed(() =>
    [...rates.value].sort((left, right) => {
        if (left.date === right.date) {
            return (left.currency?.iso_code ?? '').localeCompare(
                right.currency?.iso_code ?? '',
            );
        }

        return right.date.localeCompare(left.date);
    }),
);

function resetForm() {
    editingKey.value = null;
    form.value = {
        currency_id: props.currencies[0] ? String(props.currencies[0].id) : '',
        date: new Date().toISOString().slice(0, 10),
        rate: '',
    };
}

function editRate(rate: ExchangeRate) {
    editingKey.value = `${rate.currency?.id ?? 'unknown'}-${rate.date}`;
    form.value = {
        currency_id: String(rate.currency?.id ?? ''),
        date: rate.date,
        rate: String(rate.rate),
    };
}

async function submit() {
    if (!form.value.currency_id || !form.value.date || !form.value.rate) {
        error(t('settings.exchangeRates.saveError'));

        return;
    }

    try {
        const saved = await saveRate({
            currency_id: Number(form.value.currency_id),
            date: form.value.date,
            rate: Number(form.value.rate),
        });

        const index = rates.value.findIndex(
            (item) =>
                item.currency?.id === saved.currency?.id &&
                item.date === saved.date,
        );

        if (index === -1) {
            rates.value.push(saved);
        } else {
            rates.value.splice(index, 1, saved);
        }

        success(t('settings.exchangeRates.saved'));
        resetForm();
    } catch {
        error(t('settings.exchangeRates.saveError'));
    }
}

async function removeRate(rate: ExchangeRate) {
    try {
        await deleteRate(rate.id);
        rates.value = rates.value.filter((item) => item.id !== rate.id);

        if (
            editingKey.value ===
            `${rate.currency?.id ?? 'unknown'}-${rate.date}`
        ) {
            resetForm();
        }

        success(t('settings.exchangeRates.deleted'));
    } catch {
        error(t('settings.exchangeRates.deleteError'));
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="t('settings.exchangeRates.head')" />
        <ToastContainer />

        <SettingsLayout>
            <section
                class="space-y-6 rounded-[1.75rem] border border-border/70 bg-card/95 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)] sm:p-8"
            >
                <Heading
                    variant="small"
                    :title="t('settings.exchangeRates.title')"
                    :description="t('settings.exchangeRates.description')"
                />

                <div class="grid gap-4 lg:grid-cols-[1.2fr_1fr_1fr_auto]">
                    <label class="space-y-2 text-sm font-medium">
                        <span>{{ t('settings.exchangeRates.currency') }}</span>
                        <Select v-model="form.currency_id">
                            <SelectTrigger class="h-11 rounded-2xl">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="currency in props.currencies"
                                    :key="currency.id"
                                    :value="String(currency.id)"
                                >
                                    {{ currency.iso_code }} -
                                    {{ currency.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </label>

                    <label class="space-y-2 text-sm font-medium">
                        <span>{{ t('settings.exchangeRates.date') }}</span>
                        <Input
                            v-model="form.date"
                            type="date"
                            class="h-11 rounded-2xl"
                        />
                    </label>

                    <label class="space-y-2 text-sm font-medium">
                        <span>{{ t('settings.exchangeRates.rate') }}</span>
                        <Input
                            v-model="form.rate"
                            type="number"
                            min="0"
                            step="0.000001"
                            class="h-11 rounded-2xl"
                        />
                    </label>

                    <div class="flex items-end gap-2">
                        <Button
                            class="h-11 rounded-2xl"
                            :disabled="loading"
                            @click="submit"
                        >
                            {{ t('settings.exchangeRates.add') }}
                        </Button>
                        <Button
                            v-if="editingKey"
                            variant="outline"
                            class="h-11 rounded-2xl"
                            @click="resetForm"
                        >
                            {{ t('settings.common.cancel') }}
                        </Button>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground">
                    {{ t('settings.exchangeRates.hint') }}
                </p>

                <div
                    v-if="sortedRates.length === 0"
                    class="rounded-3xl border border-dashed border-border/70 bg-muted/20 p-6 text-center"
                >
                    <p class="text-base font-semibold">
                        {{ t('settings.exchangeRates.emptyTitle') }}
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ t('settings.exchangeRates.emptyDescription') }}
                    </p>
                </div>

                <div
                    v-else
                    class="overflow-hidden rounded-3xl border border-border/70"
                >
                    <table class="min-w-full divide-y divide-border/70 text-sm">
                        <thead class="bg-muted/30">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    {{ t('settings.exchangeRates.currency') }}
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    {{ t('settings.exchangeRates.date') }}
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    {{ t('settings.exchangeRates.rate') }}
                                </th>
                                <th class="px-4 py-3 text-right font-medium">
                                    {{ t('common.actions.edit') }} /
                                    {{ t('common.actions.delete') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <tr v-for="rate in sortedRates" :key="rate.id">
                                <td class="px-4 py-3">
                                    <div class="font-medium">
                                        {{ rate.currency?.iso_code }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ rate.currency?.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ rate.date }}</td>
                                <td class="px-4 py-3">
                                    {{ rate.rate.toFixed(6) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="rounded-2xl"
                                            @click="editRate(rate)"
                                        >
                                            {{
                                                t('settings.exchangeRates.edit')
                                            }}
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="rounded-2xl text-destructive"
                                            :disabled="loading"
                                            @click="removeRate(rate)"
                                        >
                                            {{ t('common.actions.delete') }}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </SettingsLayout>
    </AppLayout>
</template>
