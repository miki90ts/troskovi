import type { Debt } from '@/types/models';

export type RecurringTransactionFormValues = {
    type: 'income' | 'expense' | '';
    amount: string;
    description: string;
    frequency: 'daily' | 'weekly' | 'monthly' | '';
    next_due_date: string;
    category_id: string;
    bank_account_id: string;
    debt_id: string;
    payment_method: 'cash' | 'bank_account' | '';
};

export type RecurringTransactionFormErrors = Partial<
    Record<keyof RecurringTransactionFormValues, string>
>;

type RecurringTransactionValidationOptions = {
    categoryIds: number[];
    bankAccountIds: number[];
    debts: Debt[];
};

export const recurringTransactionValidationMessages = {
    typeRequired: 'Tip transakcije je obavezan.',
    typeInvalid: 'Izabrani tip transakcije nije ispravan.',
    amountRequired: 'Iznos je obavezan.',
    amountNumeric: 'Iznos mora biti broj.',
    amountGt: 'Iznos mora biti veći od 0.',
    descriptionRequired: 'Opis je obavezan.',
    descriptionString: 'Opis mora biti tekst.',
    descriptionMax: 'Opis ne sme biti duži od 255 karaktera.',
    frequencyRequired: 'Učestalost je obavezna.',
    frequencyInvalid: 'Izabrana učestalost nije ispravna.',
    nextDueDateRequired: 'Datum izvršenja je obavezan.',
    nextDueDateInvalid: 'Datum izvršenja nije ispravan.',
    categoryInteger: 'Kategorija mora biti broj.',
    categoryExists: 'Izabrana kategorija nije ispravna.',
    debtExists: 'Izabrani dug nije ispravan.',
    bankAccountInteger: 'Bankovni račun mora biti broj.',
    bankAccountExists: 'Izabrani bankovni račun nije ispravan.',
    paymentMethodRequiredIf: 'Način plaćanja je obavezan za trošak.',
    paymentMethodInvalid: 'Izabrani način plaćanja nije ispravan.',
    bankAccountRequired:
        'Bankovni račun je obavezan kada je način plaćanja bankovni račun.',
    bankAccountOnlyWithBank:
        'Bankovni račun može biti postavljen samo kada je način plaćanja bankovni račun.',
} as const;

const allowedTypes = new Set(['income', 'expense']);
const allowedFrequencies = new Set(['daily', 'weekly', 'monthly']);
const allowedPaymentMethods = new Set(['cash', 'bank_account']);

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

export function validateRecurringTransactionForm(
    values: RecurringTransactionFormValues,
    options: RecurringTransactionValidationOptions,
): RecurringTransactionFormErrors {
    const errors: RecurringTransactionFormErrors = {};

    if (isBlank(values.type)) {
        errors.type = recurringTransactionValidationMessages.typeRequired;
    } else if (!allowedTypes.has(values.type)) {
        errors.type = recurringTransactionValidationMessages.typeInvalid;
    }

    if (isBlank(values.amount)) {
        errors.amount = recurringTransactionValidationMessages.amountRequired;
    } else {
        const amount = Number(values.amount);

        if (Number.isNaN(amount)) {
            errors.amount =
                recurringTransactionValidationMessages.amountNumeric;
        } else if (amount <= 0) {
            errors.amount = recurringTransactionValidationMessages.amountGt;
        }
    }

    if (isBlank(values.description)) {
        errors.description =
            recurringTransactionValidationMessages.descriptionRequired;
    } else if (typeof values.description !== 'string') {
        errors.description =
            recurringTransactionValidationMessages.descriptionString;
    } else if (values.description.length > 255) {
        errors.description =
            recurringTransactionValidationMessages.descriptionMax;
    }

    if (isBlank(values.frequency)) {
        errors.frequency =
            recurringTransactionValidationMessages.frequencyRequired;
    } else if (!allowedFrequencies.has(values.frequency)) {
        errors.frequency =
            recurringTransactionValidationMessages.frequencyInvalid;
    }

    if (isBlank(values.next_due_date)) {
        errors.next_due_date =
            recurringTransactionValidationMessages.nextDueDateRequired;
    } else if (Number.isNaN(new Date(values.next_due_date).getTime())) {
        errors.next_due_date =
            recurringTransactionValidationMessages.nextDueDateInvalid;
    }

    const categoryId = parseOptionalInteger(values.category_id);

    if (Number.isNaN(categoryId)) {
        errors.category_id =
            recurringTransactionValidationMessages.categoryInteger;
    } else if (
        categoryId !== null &&
        !options.categoryIds.includes(categoryId)
    ) {
        errors.category_id =
            recurringTransactionValidationMessages.categoryExists;
    }

    if (values.type === 'expense' && isBlank(values.payment_method)) {
        errors.payment_method =
            recurringTransactionValidationMessages.paymentMethodRequiredIf;
    } else if (
        !isBlank(values.payment_method) &&
        !allowedPaymentMethods.has(values.payment_method)
    ) {
        errors.payment_method =
            recurringTransactionValidationMessages.paymentMethodInvalid;
    }

    const bankAccountId = parseOptionalInteger(values.bank_account_id);

    if (Number.isNaN(bankAccountId)) {
        errors.bank_account_id =
            recurringTransactionValidationMessages.bankAccountInteger;
    } else if (
        bankAccountId !== null &&
        !options.bankAccountIds.includes(bankAccountId)
    ) {
        errors.bank_account_id =
            recurringTransactionValidationMessages.bankAccountExists;
    } else if (
        values.payment_method === 'bank_account' &&
        bankAccountId === null
    ) {
        errors.bank_account_id =
            recurringTransactionValidationMessages.bankAccountRequired;
    } else if (
        values.payment_method !== '' &&
        values.payment_method !== 'bank_account' &&
        bankAccountId !== null
    ) {
        errors.bank_account_id =
            recurringTransactionValidationMessages.bankAccountOnlyWithBank;
    }

    const debtId = parseOptionalInteger(values.debt_id);

    if (Number.isNaN(debtId)) {
        errors.debt_id = recurringTransactionValidationMessages.debtExists;
    } else if (
        debtId !== null &&
        !options.debts.some((debt) => debt.id === debtId)
    ) {
        errors.debt_id = recurringTransactionValidationMessages.debtExists;
    }

    return errors;
}
