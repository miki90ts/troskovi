import { computed, ref } from 'vue';
import { useLoyaltyCards } from '@/composables/useLoyaltyCards';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type { LoyaltyCard } from '@/types/models';

export type LoyaltyCardFormState = {
    name: string;
    card_number: string;
    notes: string;
    color: string;
};

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
        showForm.value = true;
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

            showForm.value = false;
        } catch {
            showError(t('loyaltyCards.saveError'));
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
        filteredCards,
        colorPresets,
        openCreate,
        openEdit,
        openFullscreen,
        applyPresetColor,
        submitForm,
        handleDelete,
    };
}
