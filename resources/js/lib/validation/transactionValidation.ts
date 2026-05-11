import type { Debt } from '@/types/models';

export type TransactionFormValues = {
    type: 'income' | 'expense' | '';
    amount: string;
    date: string;
    description: string;
    category_id: string;
    bank_account_id: string;
    debt_id: string;
    payment_method: 'cash' | 'bank_account' | '';
    notes: string;
    is_warranty: boolean;
    receipt: File | null;
};

export type TransactionFormErrors = Partial<
    Record<keyof TransactionFormValues, string>
>;

type TransactionValidationOptions = {
    categoryIds: number[];
    bankAccountIds: number[];
    debts: Debt[];
};

export const transactionValidationMessages = {
    typeRequired: 'Tip transakcije je obavezan.',
    typeInvalid: 'Izabrani tip transakcije nije ispravan.',
    amountRequired: 'Iznos je obavezan.',
    amountNumeric: 'Iznos mora biti broj.',
    amountGt: 'Iznos mora biti veći od 0.',
    dateRequired: 'Datum je obavezan.',
    dateInvalid: 'Datum nije ispravan.',
    descriptionRequired: 'Opis je obavezan.',
    descriptionString: 'Opis mora biti tekst.',
    descriptionMax: 'Opis ne sme biti duži od 255 karaktera.',
    categoryInteger: 'Kategorija mora biti broj.',
    categoryExists: 'Izabrana kategorija nije ispravna.',
    bankAccountInteger: 'Bankovni račun mora biti broj.',
    bankAccountExists: 'Izabrani bankovni račun nije ispravan.',
    paymentMethodRequiredIf: 'Način plaćanja je obavezan za trošak.',
    paymentMethodInvalid: 'Izabrani način plaćanja nije ispravan.',
    notesString: 'Napomena mora biti tekst.',
    receiptImage: 'Potvrda mora biti slika.',
    receiptMax: 'Potvrda ne sme biti veća od 1 MB.',
    warrantyBoolean: 'Polje garancije mora biti tačno ili netačno.',
    debtExists: 'Izabrani dug nije ispravan.',
    bankAccountRequired:
        'Bankovni račun je obavezan kada je način plaćanja bankovni račun.',
    bankAccountOnlyWithBank:
        'Bankovni račun može biti postavljen samo kada je način plaćanja bankovni račun.',
} as const;

const allowedTypes = new Set(['income', 'expense']);
const allowedPaymentMethods = new Set(['cash', 'bank_account']);
const maxReceiptSizeInBytes = 1024 * 1024;

function isBlank(value: string): boolean {
    return value.trim().length === 0;
}

function parseOptionalInteger(value: string): number | null {
    if (isBlank(value)) {
        return null;
    }

    if (!/^\d+$/.test(value.trim())) {
        return Number.NaN;
    }

    return Number.parseInt(value, 10);
}

export function validateTransactionForm(
    values: TransactionFormValues,
    options: TransactionValidationOptions,
): TransactionFormErrors {
    const errors: TransactionFormErrors = {};

    if (isBlank(values.type)) {
        errors.type = transactionValidationMessages.typeRequired;
    } else if (!allowedTypes.has(values.type)) {
        errors.type = transactionValidationMessages.typeInvalid;
    }

    if (isBlank(values.amount)) {
        errors.amount = transactionValidationMessages.amountRequired;
    } else {
        const amount = Number(values.amount);

        if (Number.isNaN(amount)) {
            errors.amount = transactionValidationMessages.amountNumeric;
        } else if (amount <= 0) {
            errors.amount = transactionValidationMessages.amountGt;
        }
    }

    if (isBlank(values.date)) {
        errors.date = transactionValidationMessages.dateRequired;
    } else if (Number.isNaN(new Date(values.date).getTime())) {
        errors.date = transactionValidationMessages.dateInvalid;
    }

    if (isBlank(values.description)) {
        errors.description = transactionValidationMessages.descriptionRequired;
    } else if (typeof values.description !== 'string') {
        errors.description = transactionValidationMessages.descriptionString;
    } else if (values.description.length > 255) {
        errors.description = transactionValidationMessages.descriptionMax;
    }

    const categoryId = parseOptionalInteger(values.category_id);

    if (Number.isNaN(categoryId)) {
        errors.category_id = transactionValidationMessages.categoryInteger;
    } else if (
        categoryId !== null &&
        !options.categoryIds.includes(categoryId)
    ) {
        errors.category_id = transactionValidationMessages.categoryExists;
    }

    if (values.type === 'expense' && isBlank(values.payment_method)) {
        errors.payment_method =
            transactionValidationMessages.paymentMethodRequiredIf;
    } else if (
        !isBlank(values.payment_method) &&
        !allowedPaymentMethods.has(values.payment_method)
    ) {
        errors.payment_method =
            transactionValidationMessages.paymentMethodInvalid;
    }

    const bankAccountId = parseOptionalInteger(values.bank_account_id);

    if (Number.isNaN(bankAccountId)) {
        errors.bank_account_id =
            transactionValidationMessages.bankAccountInteger;
    } else if (
        bankAccountId !== null &&
        !options.bankAccountIds.includes(bankAccountId)
    ) {
        errors.bank_account_id =
            transactionValidationMessages.bankAccountExists;
    } else if (
        values.payment_method === 'bank_account' &&
        bankAccountId === null
    ) {
        errors.bank_account_id =
            transactionValidationMessages.bankAccountRequired;
    } else if (
        values.payment_method !== '' &&
        values.payment_method !== 'bank_account' &&
        bankAccountId !== null
    ) {
        errors.bank_account_id =
            transactionValidationMessages.bankAccountOnlyWithBank;
    }

    if (typeof values.notes !== 'string') {
        errors.notes = transactionValidationMessages.notesString;
    }

    const debtId = parseOptionalInteger(values.debt_id);

    if (Number.isNaN(debtId)) {
        errors.debt_id = transactionValidationMessages.debtExists;
    } else if (
        debtId !== null &&
        !options.debts.some((debt) => debt.id === debtId)
    ) {
        errors.debt_id = transactionValidationMessages.debtExists;
    }

    if (typeof values.is_warranty !== 'boolean') {
        errors.is_warranty = transactionValidationMessages.warrantyBoolean;
    }

    if (values.receipt) {
        if (!values.receipt.type.startsWith('image/')) {
            errors.receipt = transactionValidationMessages.receiptImage;
        } else if (values.receipt.size > maxReceiptSizeInBytes) {
            errors.receipt = transactionValidationMessages.receiptMax;
        }
    }

    return errors;
}
