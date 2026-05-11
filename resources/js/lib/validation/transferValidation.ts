import type { BankAccount } from '@/types/models';

export type TransferFormValues = {
    from_account_id: string;
    to_account_id: string;
    amount: string;
    description: string;
};

export type TransferFormErrors = Partial<
    Record<keyof TransferFormValues, string>
>;

export const transferValidationMessages = {
    fromAccountRequired: 'Izvorni račun je obavezan.',
    fromAccountExists: 'Izabrani izvorni račun nije ispravan.',
    toAccountRequired: 'Odredišni račun je obavezan.',
    toAccountExists: 'Izabrani odredišni račun nije ispravan.',
    toAccountDifferent:
        'Odredišni račun mora biti različit od izvornog računa.',
    amountRequired: 'Iznos je obavezan.',
    amountNumeric: 'Iznos mora biti broj.',
    amountGt: 'Iznos mora biti veći od 0.',
    amountInsufficient: 'Nema dovoljno sredstava na izvornom računu.',
    descriptionString: 'Opis mora biti tekst.',
    descriptionMax: 'Opis ne sme biti duži od 255 karaktera.',
} as const;

function isBlank(value: string): boolean {
    return value.trim().length === 0;
}

export function validateTransferForm(
    values: TransferFormValues,
    accounts: BankAccount[],
): TransferFormErrors {
    const errors: TransferFormErrors = {};

    const fromAccount = accounts.find(
        (account) => String(account.id) === values.from_account_id,
    );
    const toAccount = accounts.find(
        (account) => String(account.id) === values.to_account_id,
    );

    if (isBlank(values.from_account_id)) {
        errors.from_account_id = transferValidationMessages.fromAccountRequired;
    } else if (!fromAccount) {
        errors.from_account_id = transferValidationMessages.fromAccountExists;
    }

    if (isBlank(values.to_account_id)) {
        errors.to_account_id = transferValidationMessages.toAccountRequired;
    } else if (!toAccount) {
        errors.to_account_id = transferValidationMessages.toAccountExists;
    } else if (values.to_account_id === values.from_account_id) {
        errors.to_account_id = transferValidationMessages.toAccountDifferent;
    }

    if (isBlank(values.amount)) {
        errors.amount = transferValidationMessages.amountRequired;
    } else {
        const amount = Number(values.amount);

        if (Number.isNaN(amount)) {
            errors.amount = transferValidationMessages.amountNumeric;
        } else if (amount <= 0) {
            errors.amount = transferValidationMessages.amountGt;
        } else if (fromAccount && amount > fromAccount.current_balance) {
            errors.amount = transferValidationMessages.amountInsufficient;
        }
    }

    if (typeof values.description !== 'string') {
        errors.description = transferValidationMessages.descriptionString;
    } else if (values.description.length > 255) {
        errors.description = transferValidationMessages.descriptionMax;
    }

    return errors;
}
