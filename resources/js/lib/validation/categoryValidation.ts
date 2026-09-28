export type CategoryFormValues = {
    name: string;
    type: 'expense' | 'income' | '';
    icon: string;
    color: string;
};

export type CategoryFormErrors = Partial<
    Record<keyof CategoryFormValues, string>
>;

export const categoryValidationMessages = {
    nameRequired: 'Naziv kategorije je obavezan.',
    nameString: 'Naziv kategorije mora biti tekst.',
    nameMax: 'Naziv kategorije ne sme biti duži od 255 karaktera.',
    typeRequired: 'Tip kategorije je obavezan.',
    typeInvalid: 'Izabrani tip kategorije nije ispravan.',
    iconString: 'Ikonica mora biti tekst.',
    iconMax: 'Ikonica ne sme biti duža od 50 karaktera.',
    colorString: 'Boja mora biti tekst.',
    colorMax: 'Boja ne sme biti duža od 7 karaktera.',
} as const;

const allowedTypes = new Set(['expense', 'income']);

function isBlank(value: string): boolean {
    return (
        value == null ||
        (typeof value === 'string' && value.trim().length === 0)
    );
}

export function validateCategoryForm(
    values: CategoryFormValues,
): CategoryFormErrors {
    const errors: CategoryFormErrors = {};

    if (isBlank(values.name)) {
        errors.name = categoryValidationMessages.nameRequired;
    } else if (typeof values.name !== 'string') {
        errors.name = categoryValidationMessages.nameString;
    } else if (values.name.length > 255) {
        errors.name = categoryValidationMessages.nameMax;
    }

    if (isBlank(values.type)) {
        errors.type = categoryValidationMessages.typeRequired;
    } else if (!allowedTypes.has(values.type)) {
        errors.type = categoryValidationMessages.typeInvalid;
    }

    if (typeof values.icon !== 'string') {
        errors.icon = categoryValidationMessages.iconString;
    } else if (values.icon.length > 50) {
        errors.icon = categoryValidationMessages.iconMax;
    }

    if (typeof values.color !== 'string') {
        errors.color = categoryValidationMessages.colorString;
    } else if (values.color.length > 7) {
        errors.color = categoryValidationMessages.colorMax;
    }

    return errors;
}
