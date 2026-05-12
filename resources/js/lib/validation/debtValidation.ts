export type DebtFormValues = {
    type: 'i_owe' | 'owed_to_me' | '';
    person_name: string;
    description: string;
    amount: string;
    date: string;
    due_date: string;
    notes: string;
};

export type DebtFormErrors = Partial<Record<keyof DebtFormValues, string>>;

export const debtValidationMessages = {
    typeRequired: 'Tip duga je obavezan.',
    typeInvalid: 'Izabrani tip duga nije ispravan.',
    personNameRequired: 'Ime osobe je obavezno.',
    personNameString: 'Ime osobe mora biti tekst.',
    personNameMax: 'Ime osobe ne sme biti duže od 255 karaktera.',
    descriptionRequired: 'Opis je obavezan.',
    descriptionString: 'Opis mora biti tekst.',
    descriptionMax: 'Opis ne sme biti duži od 255 karaktera.',
    amountRequired: 'Iznos je obavezan.',
    amountNumeric: 'Iznos mora biti broj.',
    amountGt: 'Iznos mora biti veći od 0.',
    dateRequired: 'Datum je obavezan.',
    dateInvalid: 'Datum nije ispravan.',
    dueDateInvalid: 'Rok dospeća nije ispravan.',
    dueDateAfterOrEqual:
        'Rok dospeća mora biti isti ili nakon datuma zaduženja.',
    notesString: 'Napomena mora biti tekst.',
} as const;

const allowedDebtTypes = new Set(['i_owe', 'owed_to_me']);

function isBlank(value: string): boolean {
    return value == null || (typeof value === 'string' && value.trim().length === 0);
}

export function validateDebtForm(values: DebtFormValues): DebtFormErrors {
    const errors: DebtFormErrors = {};

    if (isBlank(values.type)) {
        errors.type = debtValidationMessages.typeRequired;
    } else if (!allowedDebtTypes.has(values.type)) {
        errors.type = debtValidationMessages.typeInvalid;
    }

    if (isBlank(values.person_name)) {
        errors.person_name = debtValidationMessages.personNameRequired;
    } else if (typeof values.person_name !== 'string') {
        errors.person_name = debtValidationMessages.personNameString;
    } else if (values.person_name.length > 255) {
        errors.person_name = debtValidationMessages.personNameMax;
    }

    if (isBlank(values.description)) {
        errors.description = debtValidationMessages.descriptionRequired;
    } else if (typeof values.description !== 'string') {
        errors.description = debtValidationMessages.descriptionString;
    } else if (values.description.length > 255) {
        errors.description = debtValidationMessages.descriptionMax;
    }

    if (isBlank(values.amount)) {
        errors.amount = debtValidationMessages.amountRequired;
    } else {
        const amount = Number(values.amount);

        if (Number.isNaN(amount)) {
            errors.amount = debtValidationMessages.amountNumeric;
        } else if (amount <= 0) {
            errors.amount = debtValidationMessages.amountGt;
        }
    }

    if (isBlank(values.date)) {
        errors.date = debtValidationMessages.dateRequired;
    } else if (Number.isNaN(new Date(values.date).getTime())) {
        errors.date = debtValidationMessages.dateInvalid;
    }

    if (!isBlank(values.due_date)) {
        const dueDate = new Date(values.due_date);

        if (Number.isNaN(dueDate.getTime())) {
            errors.due_date = debtValidationMessages.dueDateInvalid;
        } else if (
            !errors.date &&
            dueDate.getTime() < new Date(values.date).getTime()
        ) {
            errors.due_date = debtValidationMessages.dueDateAfterOrEqual;
        }
    }

    if (typeof values.notes !== 'string') {
        errors.notes = debtValidationMessages.notesString;
    }

    return errors;
}
