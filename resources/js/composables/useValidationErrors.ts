import { ref } from 'vue';

export type ValidationErrors<TField extends string = string> = Partial<
    Record<TField, string>
>;

export function useValidationErrors<TField extends string = string>() {
    const errors = ref<ValidationErrors<TField>>({});

    function setErrors(nextErrors: ValidationErrors<TField>) {
        errors.value = { ...nextErrors };
    }

    function clearAllErrors() {
        errors.value = {};
    }

    function clearErrors(...fields: TField[]) {
        for (const field of fields) {
            delete errors.value[field];
        }
    }

    function normalizeValidationErrors(
        validationErrors: Record<string, string[] | string> | undefined,
    ): ValidationErrors<TField> {
        if (!validationErrors) {
            return {};
        }

        return Object.entries(validationErrors).reduce<
            ValidationErrors<TField>
        >((carry, [key, value]) => {
            carry[key as TField] = Array.isArray(value) ? value[0] : value;

            return carry;
        }, {});
    }

    function setServerErrors(
        validationErrors: Record<string, string[] | string> | undefined,
    ) {
        errors.value = normalizeValidationErrors(validationErrors);
    }

    function fieldErrorClass(field: TField): string {
        return errors.value[field]
            ? 'border-destructive focus-visible:ring-destructive/20'
            : '';
    }

    return {
        errors,
        setErrors,
        clearAllErrors,
        clearErrors,
        setServerErrors,
        normalizeValidationErrors,
        fieldErrorClass,
    };
}
