import { computed, onMounted, ref } from 'vue';
import { useCategories } from '@/composables/useCategories';
import { useSpendingTargets } from '@/composables/useSpendingTargets';
import { useToast } from '@/composables/useToast';
import { t } from '@/lib/i18n';
import type {
    BudgetFormState,
    BudgetPeriodSection,
    BudgetStatusFilterOption,
    Category,
    SpendingTarget,
    SpendingTargetPeriod,
    SpendingTargetProgress,
    SpendingTargetProgressResponse,
    StatusFilter,
    TargetDisplayStatus,
    TargetWithProgress,
} from '@/types';

export const OVERALL_SENTINEL = '__overall__';

function createEmptyForm(
    period: SpendingTargetPeriod = 'monthly',
): BudgetFormState {
    return {
        period,
        categoryValue: OVERALL_SENTINEL,
        targetAmount: '',
        isActive: true,
    };
}

export function useBudgetsPage() {
    const periodOptions: SpendingTargetPeriod[] = [
        'daily',
        'weekly',
        'monthly',
    ];
    const { success, error: showError } = useToast();
    const { fetchCategories } = useCategories();
    const {
        fetchTargets,
        createTarget,
        updateTarget,
        deleteTarget,
        fetchProgress,
    } = useSpendingTargets();

    const loading = ref(true);
    const targets = ref<SpendingTarget[]>([]);
    const categories = ref<Category[]>([]);
    const progressByPeriod = ref<
        Partial<Record<SpendingTargetPeriod, SpendingTargetProgressResponse>>
    >({});
    const showForm = ref(false);
    const formSubmitting = ref(false);
    const editingTarget = ref<SpendingTarget | null>(null);
    const deleteCandidate = ref<SpendingTarget | null>(null);
    const statusFilter = ref<StatusFilter>('all');
    const activePeriodTab = ref<SpendingTargetPeriod>('monthly');
    const form = ref<BudgetFormState>(createEmptyForm());

    const totalCount = computed(() => targets.value.length);
    const activeCount = computed(
        () => targets.value.filter((target) => target.is_active).length,
    );
    const categoryCount = computed(
        () =>
            targets.value.filter((target) => target.scope === 'category')
                .length,
    );
    const hasTargets = computed(() => targets.value.length > 0);

    const statusFilterOptions = computed<BudgetStatusFilterOption[]>(() => [
        { value: 'all', label: t('settings.budgets.statusAll') },
        { value: 'warning', label: t('settings.budgets.statusWarning') },
        { value: 'exceeded', label: t('settings.budgets.statusExceeded') },
        { value: 'ok', label: t('settings.budgets.statusOk') },
        { value: 'inactive', label: t('settings.budgets.statusInactive') },
    ]);

    const progressMap = computed(() => {
        const entries = Object.values(progressByPeriod.value)
            .flatMap((response) => response?.targets ?? [])
            .map((progress) => [progress.id, progress] as const);

        return new Map<number, SpendingTargetProgress>(entries);
    });

    const targetsWithProgress = computed<TargetWithProgress[]>(() =>
        targets.value.map((target) => {
            const progress = progressMap.value.get(target.id) ?? null;
            const displayStatus = target.is_active
                ? (progress?.status ?? 'ok')
                : 'inactive';

            return {
                target,
                progress,
                displayStatus,
                riskScore: getRiskScore(
                    displayStatus,
                    progress?.progress_percent ?? 0,
                ),
            };
        }),
    );

    const filteredTargets = computed(() =>
        targetsWithProgress.value.filter((entry) =>
            statusFilter.value === 'all'
                ? true
                : entry.displayStatus === statusFilter.value,
        ),
    );

    const atRiskCount = computed(
        () =>
            targetsWithProgress.value.filter(
                (entry) =>
                    entry.displayStatus === 'warning' ||
                    entry.displayStatus === 'exceeded',
            ).length,
    );

    const filteredCount = computed(() => filteredTargets.value.length);

    const topRiskTargets = computed(() =>
        filteredTargets.value
            .filter(
                (entry) =>
                    entry.target.scope === 'category' && entry.target.is_active,
            )
            .sort(sortTargetsByRisk)
            .slice(0, 4),
    );

    const periodSections = computed<BudgetPeriodSection[]>(() =>
        periodOptions.map((period) => {
            const entries = filteredTargets.value
                .filter((entry) => entry.target.period === period)
                .sort(sortTargetsByRisk);

            return {
                period,
                label: t(`common.recurringFrequencies.${period}`),
                count: entries.length,
                entries,
            };
        }),
    );

    function getRiskScore(
        status: TargetDisplayStatus,
        progressPercent: number,
    ) {
        const statusWeight = {
            exceeded: 300,
            warning: 200,
            ok: 100,
            inactive: 0,
        }[status];

        return statusWeight + Math.min(progressPercent, 999);
    }

    function sortTargetsByRisk(
        left: TargetWithProgress,
        right: TargetWithProgress,
    ) {
        return right.riskScore - left.riskScore;
    }

    function openCreate(period: SpendingTargetPeriod = 'monthly') {
        editingTarget.value = null;
        form.value = createEmptyForm(period);
        showForm.value = true;
    }

    function openEdit(target: SpendingTarget) {
        editingTarget.value = target;
        form.value = {
            period: target.period,
            categoryValue: target.category
                ? String(target.category.id)
                : OVERALL_SENTINEL,
            targetAmount: String(target.target_amount),
            isActive: target.is_active,
        };
        showForm.value = true;
    }

    async function loadData() {
        loading.value = true;

        try {
            const [nextTargets, nextCategories, ...progressResponses] =
                await Promise.all([
                    fetchTargets(),
                    fetchCategories('expense'),
                    ...periodOptions.map((period) => fetchProgress(period)),
                ]);

            targets.value = nextTargets;
            categories.value = nextCategories;
            progressByPeriod.value = Object.fromEntries(
                periodOptions.map((period, index) => [
                    period,
                    progressResponses[index],
                ]),
            );
        } catch {
            showError(t('settings.budgets.loadError'));
        } finally {
            loading.value = false;
        }
    }

    async function submitForm() {
        formSubmitting.value = true;

        try {
            const payload = {
                period: form.value.period,
                target_amount: Number(form.value.targetAmount),
                category_id:
                    form.value.categoryValue === OVERALL_SENTINEL
                        ? null
                        : Number(form.value.categoryValue),
                is_active: form.value.isActive,
            };

            if (editingTarget.value) {
                await updateTarget(editingTarget.value.id, payload);
                success(t('settings.budgets.updated'));
            } else {
                await createTarget(payload);
                success(t('settings.budgets.created'));
            }

            showForm.value = false;
            await loadData();
        } catch {
            showError(t('settings.budgets.saveError'));
        } finally {
            formSubmitting.value = false;
        }
    }

    async function toggleTarget(target: SpendingTarget) {
        try {
            await updateTarget(target.id, { is_active: !target.is_active });
            success(
                t(
                    target.is_active
                        ? 'settings.budgets.deactivated'
                        : 'settings.budgets.activated',
                ),
            );
            await loadData();
        } catch {
            showError(t('settings.budgets.toggleError'));
        }
    }

    async function confirmDelete() {
        if (!deleteCandidate.value) {
            return;
        }

        try {
            await deleteTarget(deleteCandidate.value.id);
            success(t('settings.budgets.deleted'));
            deleteCandidate.value = null;
            await loadData();
        } catch {
            showError(t('settings.budgets.deleteError'));
        }
    }

    function updateForm(nextForm: BudgetFormState) {
        form.value = nextForm;
    }

    onMounted(loadData);

    return {
        periodOptions,
        loading,
        hasTargets,
        categories,
        showForm,
        formSubmitting,
        editingTarget,
        deleteCandidate,
        statusFilter,
        activePeriodTab,
        form,
        totalCount,
        activeCount,
        categoryCount,
        atRiskCount,
        statusFilterOptions,
        filteredCount,
        topRiskTargets,
        periodSections,
        openCreate,
        openEdit,
        submitForm,
        toggleTarget,
        confirmDelete,
        updateForm,
    };
}
