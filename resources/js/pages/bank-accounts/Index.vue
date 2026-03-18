<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Archive,
    Landmark,
    MoreHorizontal,
    Plus,
    RotateCcw,
    ArrowLeftRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import { useToast } from '@/composables/useToast';
import { useBankAccounts } from '@/composables/useBankAccounts';
import type { BreadcrumbItem } from '@/types';
import type { BankAccount } from '@/types/models';

const props = defineProps<{
    accounts: { data: BankAccount[] };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Bank Accounts', href: '/bank-accounts' },
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
    currency: 'USD',
    color: '#3b82f6',
    initial_balance: '0',
});

function openCreateForm() {
    editingAccount.value = null;
    accountForm.value = {
        name: '',
        bank_name: '',
        account_number: '',
        currency: 'USD',
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
            success('Account updated');
        } else {
            await createAccount(payload);
            success('Account created');
        }
        showForm.value = false;
        router.reload();
    } catch {
        showError('Failed to save account');
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
        success('Account archived');
        archiveConfirm.value = null;
        router.reload();
    } catch {
        showError('Failed to archive account');
    }
}

async function handleRestore(account: BankAccount) {
    try {
        await restoreAccount(account.id);
        success('Account restored');
        router.reload();
    } catch {
        showError('Failed to restore account');
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
        success('Transfer completed');
        showTransfer.value = false;
        router.reload();
    } catch {
        showError('Transfer failed');
    } finally {
        transferSubmitting.value = false;
    }
}

const activeAccounts = () => props.accounts.data.filter((a) => !a.is_archived);
const archivedAccounts = () => props.accounts.data.filter((a) => a.is_archived);
</script>

<template>
    <Head title="Bank Accounts" />
    <ToastContainer />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Bank Accounts
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Manage your bank accounts and transfers
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
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
                        <ArrowLeftRight class="mr-2 h-4 w-4" /> Transfer
                    </Button>
                    <Button @click="openCreateForm">
                        <Plus class="mr-2 h-4 w-4" /> Add Account
                    </Button>
                </div>
            </div>

            <!-- Active Accounts Grid -->
            <div
                v-if="
                    activeAccounts().length === 0 &&
                    archivedAccounts().length === 0
                "
            >
                <EmptyState
                    title="No bank accounts"
                    description="Add your first bank account to start tracking balances."
                >
                    <Button @click="openCreateForm">
                        <Plus class="mr-2 h-4 w-4" /> Add Account
                    </Button>
                </EmptyState>
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="account in activeAccounts()"
                    :key="account.id"
                    :href="`/bank-accounts/${account.id}`"
                    class="group relative rounded-xl border bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div class="absolute top-4 right-4" @click.prevent.stop>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8"
                                >
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="openEditForm(account)"
                                    >Edit</DropdownMenuItem
                                >
                                <DropdownMenuItem
                                    class="text-destructive"
                                    @click="archiveConfirm = account"
                                    >Archive</DropdownMenuItem
                                >
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            :style="{
                                backgroundColor:
                                    (account.color ?? '#3b82f6') + '20',
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
                            Current Balance
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

            <!-- Archived Accounts -->
            <div v-if="archivedAccounts().length > 0">
                <h2 class="mb-3 text-lg font-semibold text-muted-foreground">
                    Archived
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="account in archivedAccounts()"
                        :key="account.id"
                        class="rounded-xl border border-dashed bg-card/50 p-6 opacity-60"
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
                                @click="handleRestore(account)"
                            >
                                <RotateCcw class="mr-1 h-3 w-3" /> Restore
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Form Dialog -->
        <Dialog
            :open="showForm"
            @update:open="
                (v: boolean) => {
                    if (!v) showForm = false;
                }
            "
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle
                        >{{ editingAccount ? 'Edit' : 'New' }} Bank
                        Account</DialogTitle
                    >
                    <DialogDescription>{{
                        editingAccount
                            ? 'Update account details.'
                            : 'Add a new bank account.'
                    }}</DialogDescription>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitAccountForm">
                    <div class="grid gap-2">
                        <Label for="name">Account Name</Label>
                        <Input
                            id="name"
                            v-model="accountForm.name"
                            placeholder="e.g. Main Checking"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="bank_name">Bank Name</Label>
                        <Input
                            id="bank_name"
                            v-model="accountForm.bank_name"
                            placeholder="e.g. Chase"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="account_number">Account Number</Label>
                            <Input
                                id="account_number"
                                v-model="accountForm.account_number"
                                placeholder="Optional"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="currency">Currency</Label>
                            <Input
                                id="currency"
                                v-model="accountForm.currency"
                                placeholder="USD"
                                required
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="initial_balance">Initial Balance</Label>
                            <Input
                                id="initial_balance"
                                v-model="accountForm.initial_balance"
                                type="number"
                                step="0.01"
                                required
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="color">Color</Label>
                            <Input
                                id="color"
                                v-model="accountForm.color"
                                type="color"
                                class="h-10 p-1"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showForm = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="formSubmitting">
                            {{
                                formSubmitting
                                    ? 'Saving...'
                                    : editingAccount
                                      ? 'Update'
                                      : 'Create'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Transfer Dialog -->
        <Dialog
            :open="showTransfer"
            @update:open="
                (v: boolean) => {
                    if (!v) showTransfer = false;
                }
            "
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Transfer Between Accounts</DialogTitle>
                    <DialogDescription
                        >Move money between your bank
                        accounts.</DialogDescription
                    >
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitTransfer">
                    <div class="grid gap-2">
                        <Label>From Account</Label>
                        <select
                            v-model="transferForm.from_account_id"
                            class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm"
                            required
                        >
                            <option value="" disabled>Select account</option>
                            <option
                                v-for="a in activeAccounts()"
                                :key="a.id"
                                :value="String(a.id)"
                            >
                                {{ a.name }} ({{ a.currency }})
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label>To Account</Label>
                        <select
                            v-model="transferForm.to_account_id"
                            class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm"
                            required
                        >
                            <option value="" disabled>Select account</option>
                            <option
                                v-for="a in activeAccounts().filter(
                                    (x) =>
                                        String(x.id) !==
                                        transferForm.from_account_id,
                                )"
                                :key="a.id"
                                :value="String(a.id)"
                            >
                                {{ a.name }} ({{ a.currency }})
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="transfer_amount">Amount</Label>
                        <Input
                            id="transfer_amount"
                            v-model="transferForm.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="transfer_desc"
                            >Description (optional)</Label
                        >
                        <Input
                            id="transfer_desc"
                            v-model="transferForm.description"
                            placeholder="Reason for transfer"
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showTransfer = false"
                            >Cancel</Button
                        >
                        <Button type="submit" :disabled="transferSubmitting">
                            {{
                                transferSubmitting
                                    ? 'Transferring...'
                                    : 'Transfer'
                            }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Archive Confirm -->
        <ConfirmDialog
            :open="!!archiveConfirm"
            title="Archive Account"
            description="This account will be hidden from active views. You can restore it later."
            confirm-text="Archive"
            destructive
            @confirm="handleArchive"
            @cancel="archiveConfirm = null"
        />
    </AppLayout>
</template>
