export type BankAccount = {
    id: number;
    name: string;
    bank_name: string;
    masked_account_number: string;
    currency: string;
    color: string | null;
    icon: string | null;
    initial_balance: number;
    current_balance: number;
    is_archived: boolean;
    created_at: string;
};

export type BankAccountOverview = BankAccount & {
    total_income: number;
    total_expenses: number;
    last_transaction_date: string | null;
};

export type BankAccountSummary = {
    id: number;
    name: string;
    bank_name: string;
    currency: string;
    color: string | null;
    icon: string | null;
    current_balance: number;
};

export type CategoryCompact = {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
};

export type Category = CategoryCompact & {
    type: 'income' | 'expense';
    is_system: boolean;
};

export type Transaction = {
    id: number;
    type: 'income' | 'expense';
    amount: number;
    date: string;
    description: string;
    notes: string | null;
    payment_method: 'cash' | 'bank_account';
    receipt_url: string | null;
    category: CategoryCompact | null;
    bank_account: { id: number; name: string } | null;
    created_at: string;
};

export type AccountTransfer = {
    id: number;
    from_account: { id: number; name: string };
    to_account: { id: number; name: string };
    amount: number;
    description: string | null;
    date: string;
    created_at: string;
};

export type RecurringTransaction = {
    id: number;
    type: 'income' | 'expense';
    amount: number;
    description: string;
    frequency: 'daily' | 'weekly' | 'monthly';
    next_due_date: string;
    last_processed_date: string | null;
    payment_method: 'cash' | 'bank_account';
    is_active: boolean;
    category: CategoryCompact | null;
    bank_account: { id: number; name: string } | null;
    created_at: string;
};

export type ReportSummary = {
    total_income: number;
    total_expenses: number;
    net_savings: number;
    savings_rate: number;
    biggest_expense_category: string;
    biggest_expense_amount: number;
    mom_change: number;
    period_start: string;
    period_end: string;
};

export type SpendingTargetPeriod = 'daily' | 'weekly' | 'monthly';

export type SpendingTarget = {
    id: number;
    period: SpendingTargetPeriod;
    target_amount: number;
    is_active: boolean;
    scope: 'overall' | 'category';
    category: CategoryCompact | null;
    created_at: string;
    updated_at: string;
};

export type SpendingTargetProgress = {
    id: number;
    scope: 'overall' | 'category';
    period: SpendingTargetPeriod;
    target_amount: number;
    spent_amount: number;
    remaining_amount: number;
    progress_percent: number;
    status: 'ok' | 'warning' | 'exceeded';
    is_active: boolean;
    category: CategoryCompact | null;
};

export type SpendingTargetProgressResponse = {
    period: SpendingTargetPeriod;
    period_start: string;
    period_end: string;
    warning_threshold_percent: number;
    overall_target: SpendingTargetProgress | null;
    top_risk_target: SpendingTargetProgress | null;
    targets: SpendingTargetProgress[];
    summary: {
        active_count: number;
        warning_count: number;
        exceeded_count: number;
    };
};

export type DashboardTransaction = {
    id: number;
    type: 'income' | 'expense';
    amount: number;
    date: string;
    description: string;
    category: CategoryCompact | null;
    bank_account: { name: string } | null;
    payment_method: 'cash' | 'bank_account';
};
