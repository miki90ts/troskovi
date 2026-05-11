export type LoyaltyCardFormValues = {
    name: string;
    card_number: string;
    notes: string;
    color: string;
};

export type LoyaltyCardFormErrors = Partial<
    Record<keyof LoyaltyCardFormValues, string>
>;

export const loyaltyCardValidationMessages = {
    nameRequired: 'Naziv kartice je obavezan.',
    nameString: 'Naziv kartice mora biti tekst.',
    nameMax: 'Naziv kartice ne sme biti duži od 255 karaktera.',
    cardNumberRequired: 'Broj kartice je obavezan.',
    cardNumberString: 'Broj kartice mora biti tekst.',
    cardNumberMax: 'Broj kartice ne sme biti duži od 100 karaktera.',
    notesString: 'Napomena mora biti tekst.',
    notesMax: 'Napomena ne sme biti duža od 1000 karaktera.',
    colorString: 'Boja mora biti tekst.',
    colorMax: 'Boja ne sme biti duža od 7 karaktera.',
} as const;

function isBlank(value: string): boolean {
    return value.trim().length === 0;
}

export function validateLoyaltyCardForm(
    values: LoyaltyCardFormValues,
): LoyaltyCardFormErrors {
    const errors: LoyaltyCardFormErrors = {};

    if (isBlank(values.name)) {
        errors.name = loyaltyCardValidationMessages.nameRequired;
    } else if (typeof values.name !== 'string') {
        errors.name = loyaltyCardValidationMessages.nameString;
    } else if (values.name.length > 255) {
        errors.name = loyaltyCardValidationMessages.nameMax;
    }

    if (isBlank(values.card_number)) {
        errors.card_number = loyaltyCardValidationMessages.cardNumberRequired;
    } else if (typeof values.card_number !== 'string') {
        errors.card_number = loyaltyCardValidationMessages.cardNumberString;
    } else if (values.card_number.length > 100) {
        errors.card_number = loyaltyCardValidationMessages.cardNumberMax;
    }

    if (typeof values.notes !== 'string') {
        errors.notes = loyaltyCardValidationMessages.notesString;
    } else if (values.notes.length > 1000) {
        errors.notes = loyaltyCardValidationMessages.notesMax;
    }

    if (typeof values.color !== 'string') {
        errors.color = loyaltyCardValidationMessages.colorString;
    } else if (values.color.length > 7) {
        errors.color = loyaltyCardValidationMessages.colorMax;
    }

    return errors;
}
