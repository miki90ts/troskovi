<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Archive,
    CircleDollarSign,
    Landmark,
    MoreHorizontal,
    Plus,
    RotateCcw,
    ArrowLeftRight,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrencyDisplay from '@/components/CurrencyDisplay.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useToast } from '@/composables/useToast';
import { useBankAccounts } from '@/composables/useBankAccounts';
import { formatCurrency, t } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    accounts: { data: BankAccount[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('app.nav.dashboard'), href: '/dashboard' },
    { title: t('app.nav.bankAccounts'), href: '/bank-accounts' },
];

const { success, error: showError } = useToast();
const {
    createAccount,
    updateAccount,
    archiveAccount,
    restoreAccount,
    transferFunds,
} = useBankAccounts();

// Account form
const showForm = ref(false);
const editingAccount = ref<BankAccount | null>(null);
const formSubmitting = ref(false);
const accountForm = ref({
    name: '',
    bank_name: '',
    account_number: '',
    currency: 'RSD',
    color: '#3b82f6',
    initial_balance: '0',
});

function openCreateForm() {
    editingAccount.value = null;
    accountForm.value = {
        name: '',
        bank_name: '',
        account_number: '',
        currency: 'RSD',
        color: '#3b82f6',
        initial_balance: '0',
    };
    showForm.value = true;
}

function openEditForm(account: BankAccount) {
    editingAccount.value = account;
    accountForm.value = {
        name: account.name,
        bank_name: account.bank_name,
        account_number: '',
        currency: account.currency,
        color: account.color ?? '#3b82f6',
        initial_balance: String(account.initial_balance),
    };
    showForm.value = true;
}

async function submitAccountForm() {
    formSubmitting.value = true;
    try {
        const payload = {
            ...accountForm.value,
            initial_balance: parseFloat(accountForm.value.initial_balance),
        };
        if (editingAccount.value) {
            await updateAccount(editingAccount.value.id, payload);
            success(t('finance.bankAccounts.updated'));
        } else {
            await createAccount(payload);
            success(t('finance.bankAccounts.created'));
        }
        showForm.value = false;
        router.reload();
    } catch {
        showError(t('finance.bankAccounts.saveError'));
    } finally {
        formSubmitting.value = false;
    }
}

// Archive/restore
const archiveConfirm = ref<BankAccount | null>(null);

async function handleArchive() {
    if (!archiveConfirm.value) return;
    try {
        await archiveAccount(archiveConfirm.value.id);
        success(t('finance.bankAccounts.archivedSuccess'));
        archiveConfirm.value = null;
        router.reload();
    } catch {
        showError(t('finance.bankAccounts.archiveError'));
    }
}

async function handleRestore(account: BankAccount) {
    try {
        await restoreAccount(account.id);
        success(t('finance.bankAccounts.restoredSuccess'));
        router.reload();
    } catch {
        showError(t('finance.bankAccounts.restoreError'));
    }
}

// Transfer
const showTransfer = ref(false);
const transferForm = ref({
    from_account_id: '',
    to_account_id: '',
    amount: '',
    description: '',
});
const transferSubmitting = ref(false);

const activeAccounts = computed(() =>
    props.accounts.data.filter((account) => !account.is_archived),
);

const archivedAccounts = computed(() =>
    props.accounts.data.filter((account) => account.is_archived),
);

const totalBalance = computed(() =>
    activeAccounts.value.reduce(
        (sum, account) => sum + account.current_balance,
        0,
    ),
);

const connectedBanks = computed(
    () =>
        new Set(activeAccounts.value.map((account) => account.bank_name)).size,
);

async function submitTransfer() {
    transferSubmitting.value = true;
    try {
        await transferFunds({
            from_account_id: parseInt(transferForm.value.from_account_id),
            to_account_id: parseInt(transferForm.value.to_account_id),
            amount: parseFloat(transferForm.value.amount),
            description: transferForm.value.description || undefined,
            date: new Date().toISOString().split('T')[0],
        });
        success(t('finance.bankAccounts.transferSuccess'));
        showTransfer.value = false;
        router.reload();
    } catch {
        showError(t('finance.bankAccounts.transferError'));
    } finally {
        transferSubmitting.value = false;
    }
}

const accountColorPresets = [
    '#14b8a6',
    '#10b981',
    '#3b82f6',
    '#f97316',
    '#ef4444',
    '#8b5cf6',
];

function applyAccountColor(color: string) {
    accountForm.value.color = color;
}
</script>

<template>
    <Head :title="t('finance.bankAccounts.head')" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden rounded-3xl border border-border/60 bg-card p-6 shadow-sm"
            >
                <div
                    class="absolute -top-16 -left-12 h-48 w-48 rounded-full bg-primary/15 blur-3xl"
                />
                <div
                    class="absolute right-0 bottom-0 hidden h-56 w-56 rounded-full bg-emerald-300/10 blur-3xl lg:block"
                />
                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-2xl space-y-4">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.bankAccounts.badge') }}
                        </div>
                        <div class="space-y-2">
                            <h1
                                class="text-3xl font-semibold tracking-tight text-foreground"
                            >
                                {{ t('finance.bankAccounts.heroTitle') }}
                            </h1>
                            <p
                                class="max-w-xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ t('finance.bankAccounts.heroDescription') }}
                            </p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t('finance.bankAccounts.activeAccounts')
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ activeAccounts.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{ t('finance.bankAccounts.archived') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ archivedAccounts.length }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-border/60 bg-background/80 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    {{
                                        t(
                                            'finance.bankAccounts.totalNetworkBalance',
                                        )
                                    }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-semibold text-foreground"
                                >
                                    {{ formatCurrency(totalBalance) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-md">
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{ t('finance.bankAccounts.banks') }}</span
                                >
                                <Landmark class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-lg font-semibold">
                                {{ connectedBanks }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.bankAccounts.transfers')
                                    }}</span
                                >
                                <ArrowLeftRight class="h-4 w-4 text-primary" />
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                {{
                                    t(
                                        'finance.bankAccounts.transfersDescription',
                                    )
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-border/60 bg-background/85 p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >{{
                                        t('finance.bankAccounts.visibility')
                                    }}</span
                                >
                                <CircleDollarSign
                                    class="h-4 w-4 text-primary"
                                />
                            </div>
                            <p class="mt-3 text-sm font-semibold">
                                {{
                                    t(
                                        'finance.bankAccounts.visibilityDescription',
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <Button
                        variant="outline"
                        class="h-11 rounded-2xl border-border/60 px-4"
                        @click="
                            showTransfer = true;
                            transferForm = {
                                from_account_id: '',
                                to_account_id: '',
                                amount: '',
                                description: '',
                            };
                        "
                    >
                        <ArrowLeftRight class="mr-2 h-4 w-4" />
                        {{ t('common.actions.transfer') }}
                    </Button>
                    <Button
                        class="h-11 rounded-2xl px-5"
                        @click="openCreateForm"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('finance.bankAccounts.add') }}
                    </Button>
                </div>
            </section>

            <div
                v-if="
                    activeAccounts.length === 0 && archivedAccounts.length === 0
                "
            >
                <EmptyState
                    :title="t('finance.bankAccounts.emptyTitle')"
                    :description="t('finance.bankAccounts.emptyDescription')"
                >
                    <Button class="rounded-2xl px-5" @click="openCreateForm">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ t('finance.bankAccounts.add') }}
                    </Button>
                </EmptyState>
            </div>

            <section
                v-else
                class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
            >
                <div
                    class="mb-5 flex flex-col gap-3 rounded-3xl border border-border/60 bg-background/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-muted-foreground uppercase"
                        >
                            {{ t('finance.bankAccounts.overviewTitle') }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold tracking-tight">
                            {{ t('finance.bankAccounts.overviewDescription') }}
                        </h2>
                    </div>
                    <div
                        class="flex items-center gap-3 text-sm text-muted-foreground"
                    >
                        <span>{{
                            t('finance.bankAccounts.activeCount', {
                                count: activeAccounts.length,
                            })
                        }}</span>
                        <span
                            class="hidden h-1 w-1 rounded-full bg-border sm:block"
                        />
                        <span>{{
                            t('finance.bankAccounts.banksCount', {
                                count: connectedBanks,
                            })
                        }}</span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="account in activeAccounts"
                        :key="account.id"
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
                                    <DropdownMenuItem
                                        @click="openEditForm(account)"
                                        >{{
                                            t('finance.bankAccounts.editMenu')
                                        }}</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="archiveConfirm = account"
                                        >{{
                                            t(
                                                'finance.bankAccounts.archiveMenu',
                                            )
                                        }}</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm"
                                :style="{
                                    backgroundColor:
                                        (account.color ?? '#3b82f6') + '20',
                                }"
                            >
                                <Landmark
                                    class="h-5 w-5"
                                    :style="{
                                        color: account.color ?? '#3b82f6',
                                    }"
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
                </div>
            </section>

            <section
                v-if="archivedAccounts.length > 0"
                class="rounded-3xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
            >
                <h2 class="mb-3 text-lg font-semibold text-muted-foreground">
                    {{ t('finance.bankAccounts.archived') }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="account in archivedAccounts"
                        :key="account.id"
                        class="rounded-3xl border border-dashed border-border/70 bg-background/60 p-6 opacity-70"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Archive
                                    class="h-5 w-5 text-muted-foreground"
                                />
                                <div>
                                    <p class="font-medium">
                                        {{ account.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ account.bank_name }}
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="rounded-2xl"
                                @click="handleRestore(account)"
                            >
                                <RotateCcw class="mr-1 h-3 w-3" />
                                {{ t('finance.bankAccounts.restore') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <Dialog
            :open="showForm"
            @update:open="
                (v: boolean) => {
                    if (!v) showForm = false;
                }
            "
        >
            <DialogContent
                class="rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-xl"
            >
                <DialogHeader>
                    <div
                        class="relative overflow-hidden border-b border-border/60 bg-card px-6 py-5"
                    >
                        <div
                            class="absolute -top-10 left-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"
                        />
                        <div
                            class="absolute right-4 bottom-0 h-24 w-24 rounded-full bg-emerald-300/10 blur-3xl"
                        />
                        <div
                            class="relative inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.bankAccounts.accountEditBadge') }}
                        </div>
                        <DialogTitle
                            class="relative mt-4 text-2xl tracking-tight"
                        >
                            {{
                                editingAccount
                                    ? t('finance.bankAccounts.editTitle')
                                    : t('finance.bankAccounts.newTitle')
                            }}
                        </DialogTitle>
                        <DialogDescription
                            class="relative mt-2 max-w-lg text-sm leading-6"
                        >
                            {{
                                editingAccount
                                    ? t('finance.bankAccounts.editDescription')
                                    : t(
                                          'finance.bankAccounts.createDescription',
                                      )
                            }}
                        </DialogDescription>
                    </div>
                </DialogHeader>
                <form
                    class="space-y-6 px-6 py-6"
                    @submit.prevent="submitAccountForm"
                >
                    <div class="grid gap-2">
                        <Label
                            for="name"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.accountName') }}</Label
                        >
                        <Input
                            id="name"
                            v-model="accountForm.name"
                            :placeholder="
                                t('finance.bankAccounts.accountNamePlaceholder')
                            "
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="bank_name"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.bankName') }}</Label
                        >
                        <Input
                            id="bank_name"
                            v-model="accountForm.bank_name"
                            :placeholder="
                                t('finance.bankAccounts.bankNamePlaceholder')
                            "
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label
                                for="account_number"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                                >{{
                                    t('finance.bankAccounts.accountNumber')
                                }}</Label
                            >
                            <Input
                                id="account_number"
                                v-model="accountForm.account_number"
                                :placeholder="
                                    t('finance.bankAccounts.optional')
                                "
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="currency"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                                >{{ t('finance.bankAccounts.currency') }}</Label
                            >
                            <Input
                                id="currency"
                                v-model="accountForm.currency"
                                placeholder="RSD"
                                class="h-11 rounded-2xl border-border/60 bg-background"
                                required
                            />
                        </div>
                    </div>
                    <div
                        class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end"
                    >
                        <div class="grid gap-2">
                            <Label
                                for="initial_balance"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                                >{{
                                    t('finance.bankAccounts.initialBalance')
                                }}</Label
                            >
                            <Input
                                id="initial_balance"
                                v-model="accountForm.initial_balance"
                                type="number"
                                step="0.01"
                                class="h-11 rounded-2xl border-border/60 bg-background"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label
                                for="color"
                                class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                                >{{
                                    t('finance.bankAccounts.chooseColor')
                                }}</Label
                            >
                            <Input
                                id="color"
                                v-model="accountForm.color"
                                type="color"
                                class="h-14 w-full rounded-2xl border-border/60 bg-background p-2 md:w-24"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.quickChoices') }}</Label
                        >
                        <div
                            class="flex flex-wrap gap-2 rounded-3xl border border-dashed border-border/70 bg-muted/20 p-3"
                        >
                            <button
                                v-for="preset in accountColorPresets"
                                :key="preset"
                                type="button"
                                class="h-10 w-10 rounded-2xl border border-white/40 shadow-sm transition hover:scale-105"
                                :style="{ backgroundColor: preset }"
                                @click="applyAccountColor(preset)"
                            />
                        </div>
                    </div>
                    <DialogFooter class="border-t border-border/60 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="rounded-2xl"
                            @click="showForm = false"
                        >
                            {{ t('common.actions.cancel') }}
                        </Button>
                        <Button
                            class="rounded-2xl px-5"
                            type="submit"
                            :disabled="formSubmitting"
                        >
                            {{
                                formSubmitting
                                    ? t('finance.bankAccounts.saving')
                                    : editingAccount
                                      ? t('common.actions.update')
                                      : t('common.actions.create')
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="showTransfer"
            @update:open="
                (v: boolean) => {
                    if (!v) showTransfer = false;
                }
            "
        >
            <DialogContent
                class="rounded-3xl border border-border/60 bg-background/95 p-0 shadow-2xl sm:max-w-xl"
            >
                <DialogHeader>
                    <div
                        class="relative overflow-hidden border-b border-border/60 bg-card px-6 py-5"
                    >
                        <div
                            class="absolute -top-10 left-0 h-32 w-32 rounded-full bg-primary/15 blur-3xl"
                        />
                        <div
                            class="absolute right-4 bottom-0 h-24 w-24 rounded-full bg-emerald-300/10 blur-3xl"
                        />
                        <div
                            class="relative inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-primary uppercase"
                        >
                            {{ t('finance.bankAccounts.transferBadge') }}
                        </div>
                        <DialogTitle
                            class="relative mt-4 text-2xl tracking-tight"
                            >{{
                                t('finance.bankAccounts.transferTitle')
                            }}</DialogTitle
                        >
                        <DialogDescription
                            class="relative mt-2 max-w-lg text-sm leading-6"
                            >{{
                                t('finance.bankAccounts.transferDescription')
                            }}</DialogDescription
                        >
                    </div>
                </DialogHeader>
                <form
                    class="space-y-6 px-6 py-6"
                    @submit.prevent="submitTransfer"
                >
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.fromAccount') }}</Label
                        >
                        <Select v-model="transferForm.from_account_id">
                            <SelectTrigger
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('finance.bankAccounts.selectAccount')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="a in activeAccounts"
                                    :key="a.id"
                                    :value="String(a.id)"
                                >
                                    {{ a.name }} ({{ a.currency }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('finance.bankAccounts.toAccount') }}</Label
                        >
                        <Select v-model="transferForm.to_account_id">
                            <SelectTrigger
                                class="h-11 rounded-2xl border-border/60 bg-background"
                            >
                                <SelectValue
                                    :placeholder="
                                        t('finance.bankAccounts.selectAccount')
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="a in activeAccounts.filter(
                                        (x) =>
                                            String(x.id) !==
                                            transferForm.from_account_id,
                                    )"
                                    :key="a.id"
                                    :value="String(a.id)"
                                >
                                    {{ a.name }} ({{ a.currency }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="transfer_amount"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{ t('common.labels.amount') }}</Label
                        >
                        <Input
                            id="transfer_amount"
                            v-model="transferForm.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="h-11 rounded-2xl border-border/60 bg-background"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="transfer_desc"
                            class="text-xs tracking-[0.18em] text-muted-foreground uppercase"
                            >{{
                                t(
                                    'finance.bankAccounts.transferDescriptionLabel',
                                )
                            }}</Label
                        >
                        <Input
                            id="transfer_desc"
                            v-model="transferForm.description"
                            :placeholder="
                                t(
                                    'finance.bankAccounts.transferDescriptionPlaceholder',
                                )
                            "
                            class="h-11 rounded-2xl border-border/60 bg-background"
                        />
                    </div>
                    <DialogFooter class="border-t border-border/60 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            class="rounded-2xl"
                            @click="showTransfer = false"
                        >
                            {{ t('common.actions.cancel') }}
                        </Button>
                        <Button
                            class="rounded-2xl px-5"
                            type="submit"
                            :disabled="transferSubmitting"
                        >
                            {{
                                transferSubmitting
                                    ? t('finance.bankAccounts.transfering')
                                    : t('common.actions.transfer')
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Archive Confirm -->
        <ConfirmDialog
            :open="!!archiveConfirm"
            :title="t('finance.bankAccounts.archiveTitle')"
            :description="t('finance.bankAccounts.archiveDescription')"
            :confirm-text="t('finance.bankAccounts.archiveMenu')"
            destructive
            @confirm="handleArchive"
            @cancel="archiveConfirm = null"
        />
    </AppLayout>
</template>
