import type {
    SpendingTarget,
    SpendingTargetPeriod,
    SpendingTargetProgress,
} from './models';

export type TargetDisplayStatus = SpendingTargetProgress['status'] | 'inactive';
export type StatusFilter = 'all' | 'ok' | 'warning' | 'exceeded' | 'inactive';

export type TargetWithProgress = {
    target: SpendingTarget;
    progress: SpendingTargetProgress | null;
    displayStatus: TargetDisplayStatus;
    riskScore: number;
};

export type BudgetStatusFilterOption = {
    value: StatusFilter;
    label: string;
};

export type BudgetPeriodSection = {
    period: SpendingTargetPeriod;
    label: string;
    count: number;
    entries: TargetWithProgress[];
};

export type BudgetFormState = {
    period: SpendingTargetPeriod;
    categoryValue: string;
    targetAmount: string;
    isActive: boolean;
};
