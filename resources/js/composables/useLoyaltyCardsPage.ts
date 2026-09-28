import { computed, ref } from 'vue';
import { useLoyaltyCards } from '@/composables/useLoyaltyCards';
import { useToast } from '@/composables/useToast';
import { useValidationErrors } from '@/composables/useValidationErrors';
import { t } from '@/lib/i18n';
import { validateLoyaltyCardForm } from '@/lib/validation/loyaltyCardValidation';
import type { LoyaltyCardFormValues } from '@/lib/validation/loyaltyCardValidation';
import type { LoyaltyCard } from '@/types/models';

export type LoyaltyCardFormState = LoyaltyCardFormValues;

function createEmptyForm(): LoyaltyCardFormState {
    return {
        name: '',
        card_number: '',
        notes: '',
        color: '#14b8a6',
    };
}

export function useLoyaltyCardsPage(initialCards: LoyaltyCard[]) {
    const { success, error: showError } = useToast();
    const { createCard, updateCard, deleteCard } = useLoyaltyCards();

    const cards = ref<LoyaltyCard[]>([...initialCards]);
    const showForm = ref(false);
    const editingCard = ref<LoyaltyCard | null>(null);
    const formSubmitting = ref(false);
    const deleteTarget = ref<LoyaltyCard | null>(null);
    const fullscreenCard = ref<LoyaltyCard | null>(null);
    const searchQuery = ref('');
    const cardForm = ref<LoyaltyCardFormState>(createEmptyForm());
    const {
        errors: formErrors,
        clearAllErrors,
        clearErrors,
        setErrors,
        setServerErrors,
    } = useValidationErrors<keyof LoyaltyCardFormState>();

    const filteredCards = computed(() => {
        if (!searchQuery.value.trim()) {
            return cards.value;
        }

        const query = searchQuery.value.toLowerCase();

        return cards.value.filter(
            (card) =>
                card.name.toLowerCase().includes(query) ||
                card.card_number.toLowerCase().includes(query),
        );
    });

    const colorPresets = [
        '#14b8a6',
        '#10b981',
        '#3b82f6',
        '#f97316',
        '#ef4444',
        '#8b5cf6',
    ];

    function openCreate() {
        editingCard.value = null;
        cardForm.value = createEmptyForm();
        clearAllErrors();
        showForm.value = true;
    }

    function openEdit(card: LoyaltyCard) {
        editingCard.value = card;
        cardForm.value = {
            name: card.name,
            card_number: card.card_number,
            notes: card.notes ?? '',
            color: card.color ?? '#14b8a6',
        };
        clearAllErrors();
        showForm.value = true;
    }

    function setCardForm(value: LoyaltyCardFormState) {
        const previous = cardForm.value;
        cardForm.value = value;

        const changedFields = (
            Object.keys(value) as (keyof LoyaltyCardFormState)[]
        ).filter((field) => previous[field] !== value[field]);

        if (changedFields.length > 0) {
            clearErrors(...changedFields);
        }
    }

    function closeForm() {
        showForm.value = false;
        clearAllErrors();
    }

    function openFullscreen(card: LoyaltyCard) {
        fullscreenCard.value = card;
    }

    function applyPresetColor(color: string) {
        cardForm.value.color = color;
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            clearAllErrors();

            const frontErrors = validateLoyaltyCardForm(cardForm.value);

            if (Object.keys(frontErrors).length > 0) {
                setErrors(frontErrors);

                return;
            }

            const payload = {
                name: cardForm.value.name,
                card_number: cardForm.value.card_number,
                notes: cardForm.value.notes || null,
                color: cardForm.value.color || null,
            };

            if (editingCard.value) {
                const updated = await updateCard(editingCard.value.id, payload);
                const index = cards.value.findIndex(
                    (c) => c.id === editingCard.value!.id,
                );

                if (index !== -1) {
                    cards.value.splice(index, 1, updated);
                }

                success(t('loyaltyCards.updated'));
            } else {
                const created = await createCard(payload);
                cards.value.push(created);
                success(t('loyaltyCards.created'));
            }

            closeForm();
        } catch (e: any) {
            if (e.response?.status === 422) {
                setServerErrors(e.response?.data?.errors);
            } else {
                showError(t('loyaltyCards.saveError'));
            }
        } finally {
            formSubmitting.value = false;
        }
    }

    async function handleDelete() {
        if (!deleteTarget.value) {
            return;
        }

        try {
            const id = deleteTarget.value.id;
            await deleteCard(id);
            cards.value = cards.value.filter((c) => c.id !== id);
            success(t('loyaltyCards.deleted'));
            deleteTarget.value = null;
        } catch {
            showError(t('loyaltyCards.deleteError'));
        }
    }

    return {
        cards,
        showForm,
        editingCard,
        formSubmitting,
        deleteTarget,
        fullscreenCard,
        searchQuery,
        cardForm,
        formErrors,
        filteredCards,
        colorPresets,
        openCreate,
        openEdit,
        openFullscreen,
        setCardForm,
        closeForm,
        applyPresetColor,
        submitForm,
        handleDelete,
    };
}
