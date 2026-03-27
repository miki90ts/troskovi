import { computed, toRef } from 'vue';
import { t } from '@/lib/i18n';
import type {
    BankAccountSummary,
    DashboardTransaction,
    ReportSummary,
    SpendingTargetProgress,
    SpendingTargetProgressResponse,
} from '@/types';

type DashboardProps = {
    summary: ReportSummary;
    budgetProgress: SpendingTargetProgressResponse;
    recentTransactions: DashboardTransaction[];
    accounts: BankAccountSummary[];
};

export function useDashboardPage(props: DashboardProps) {
    const budgetProgress = toRef(props, 'budgetProgress');

    const secondaryBudgetTarget = computed(() => {
        if (!budgetProgress.value.top_risk_target) {
            return null;
        }

        if (
            budgetProgress.value.top_risk_target.scope === 'overall' &&
            budgetProgress.value.targets.length === 1
        ) {
            return null;
        }

        return budgetProgress.value.top_risk_target;
    });

    function budgetLabel(target: SpendingTargetProgress) {
        return target.category?.name ?? t('dashboard.budgets.overall');
    }

    function budgetStatusLabel(status: SpendingTargetProgress['status']) {
        return t(
            `dashboard.budgets.status${status.charAt(0).toUpperCase()}${status.slice(1)}`,
        );
    }

    function budgetStatusClass(status: SpendingTargetProgress['status']) {
        return {
            exceeded:
                'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
            warning:
                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            ok: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        }[status];
    }

    function budgetBarClass(status: SpendingTargetProgress['status']) {
        return {
            exceeded: 'bg-rose-500',
            warning: 'bg-amber-500',
            ok: 'bg-emerald-500',
        }[status];
    }

    function budgetBarWidth(progressPercent: number) {
        return `${Math.min(progressPercent, 100)}%`;
    }

    return {
        secondaryBudgetTarget,
        budgetLabel,
        budgetStatusLabel,
        budgetStatusClass,
        budgetBarClass,
        budgetBarWidth,
    };
}
