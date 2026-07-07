import { OVERALL_SENTINEL } from '@/composables/useBudgetsPage';
import type { BudgetFormState, Category } from '@/types';

export type BudgetFormErrors = Partial<Record<keyof BudgetFormState, string>>;

export const spendingTargetValidationMessages = {
    periodRequired: 'Period budžeta je obavezan.',
    periodInvalid: 'Izabrani period budžeta nije ispravan.',
    targetAmountRequired: 'Ciljni iznos je obavezan.',
    targetAmountNumeric: 'Ciljni iznos mora biti broj.',
    targetAmountGt: 'Ciljni iznos mora biti veći od 0.',
    currencyInteger: 'Valuta mora biti broj.',
    categoryInteger: 'Kategorija mora biti broj.',
    categoryExists: 'Izabrana kategorija nije ispravna.',
    duplicateScopePeriod: 'Budžet za izabrani opseg i period već postoji.',
} as const;

const allowedPeriods = new Set(['daily', 'weekly', 'monthly']);

function isBlank(value: string): boolean {
    return (
        value == null ||
        (typeof value === 'string' && value.trim().length === 0)
    );
}

export function validateSpendingTargetForm(
    values: BudgetFormState,
    categories: Category[],
): BudgetFormErrors {
    const errors: BudgetFormErrors = {};

    if (!allowedPeriods.has(values.period)) {
        errors.period = isBlank(values.period)
            ? spendingTargetValidationMessages.periodRequired
            : spendingTargetValidationMessages.periodInvalid;
    }

    if (isBlank(values.targetAmount)) {
        errors.targetAmount =
            spendingTargetValidationMessages.targetAmountRequired;
    } else {
        const targetAmount = Number(values.targetAmount);

        if (Number.isNaN(targetAmount)) {
            errors.targetAmount =
                spendingTargetValidationMessages.targetAmountNumeric;
        } else if (targetAmount <= 0) {
            errors.targetAmount =
                spendingTargetValidationMessages.targetAmountGt;
        }
    }

    if (!isBlank(values.currency_id) && !/^\d+$/.test(values.currency_id)) {
        errors.currency_id = spendingTargetValidationMessages.currencyInteger;
    }

    if (values.categoryValue !== OVERALL_SENTINEL) {
        if (!/^\d+$/.test(values.categoryValue)) {
            errors.categoryValue =
                spendingTargetValidationMessages.categoryInteger;
        } else if (
            !categories.some(
                (category) => String(category.id) === values.categoryValue,
            )
        ) {
            errors.categoryValue =
                spendingTargetValidationMessages.categoryExists;
        }
    }

    return errors;
}
