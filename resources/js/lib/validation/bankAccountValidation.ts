export type BankAccountFormValues = {
    name: string;
    bank_name: string;
    account_number: string;
    currency: string;
    color: string;
    initial_balance: string;
};

export type BankAccountFormErrors = Partial<
    Record<keyof BankAccountFormValues, string>
>;

export const bankAccountValidationMessages = {
    nameRequired: 'Naziv računa je obavezan.',
    nameString: 'Naziv računa mora biti tekst.',
    nameMax: 'Naziv računa ne sme biti duži od 255 karaktera.',
    bankNameRequired: 'Naziv banke je obavezan.',
    bankNameString: 'Naziv banke mora biti tekst.',
    bankNameMax: 'Naziv banke ne sme biti duži od 255 karaktera.',
    accountNumberString: 'Broj računa mora biti tekst.',
    accountNumberMax: 'Broj računa ne sme biti duži od 50 karaktera.',
    currencyRequired: 'Valuta je obavezna.',
    currencyString: 'Valuta mora biti tekst.',
    currencyMax: 'Valuta ne sme biti duža od 10 karaktera.',
    colorString: 'Boja mora biti tekst.',
    colorMax: 'Boja ne sme biti duža od 7 karaktera.',
    initialBalanceRequired: 'Početno stanje je obavezno.',
    initialBalanceNumeric: 'Početno stanje mora biti broj.',
    initialBalanceMin: 'Početno stanje ne sme biti negativno.',
} as const;

function isBlank(value: string): boolean {
    return value.trim().length === 0;
}

export function validateBankAccountForm(
    values: BankAccountFormValues,
): BankAccountFormErrors {
    const errors: BankAccountFormErrors = {};

    if (isBlank(values.name)) {
        errors.name = bankAccountValidationMessages.nameRequired;
    } else if (typeof values.name !== 'string') {
        errors.name = bankAccountValidationMessages.nameString;
    } else if (values.name.length > 255) {
        errors.name = bankAccountValidationMessages.nameMax;
    }

    if (isBlank(values.bank_name)) {
        errors.bank_name = bankAccountValidationMessages.bankNameRequired;
    } else if (typeof values.bank_name !== 'string') {
        errors.bank_name = bankAccountValidationMessages.bankNameString;
    } else if (values.bank_name.length > 255) {
        errors.bank_name = bankAccountValidationMessages.bankNameMax;
    }

    if (typeof values.account_number !== 'string') {
        errors.account_number =
            bankAccountValidationMessages.accountNumberString;
    } else if (values.account_number.length > 50) {
        errors.account_number = bankAccountValidationMessages.accountNumberMax;
    }

    if (isBlank(values.currency)) {
        errors.currency = bankAccountValidationMessages.currencyRequired;
    } else if (typeof values.currency !== 'string') {
        errors.currency = bankAccountValidationMessages.currencyString;
    } else if (values.currency.length > 10) {
        errors.currency = bankAccountValidationMessages.currencyMax;
    }

    if (typeof values.color !== 'string') {
        errors.color = bankAccountValidationMessages.colorString;
    } else if (values.color.length > 7) {
        errors.color = bankAccountValidationMessages.colorMax;
    }

    if (isBlank(values.initial_balance)) {
        errors.initial_balance =
            bankAccountValidationMessages.initialBalanceRequired;
    } else {
        const initialBalance = Number(values.initial_balance);

        if (Number.isNaN(initialBalance)) {
            errors.initial_balance =
                bankAccountValidationMessages.initialBalanceNumeric;
        } else if (initialBalance < 0) {
            errors.initial_balance =
                bankAccountValidationMessages.initialBalanceMin;
        }
    }

    return errors;
}
