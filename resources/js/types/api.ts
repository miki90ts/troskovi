export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type PaginatedResponse<T> = {
    data: T[];
    meta: PaginationMeta;
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
};

export type ApiResponse<T> = {
    data: T;
};

export type TransactionFilters = {
    type?: 'income' | 'expense';
    date_from?: string;
    date_to?: string;
    category_id?: number | string;
    category_ids?: string;
    bank_account_id?: number | string;
    payment_method?: 'cash' | 'bank_account';
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
};

export type ReportPeriod = 'weekly' | 'monthly' | 'yearly';

export type SpendingTargetPayload = {
    period: 'daily' | 'weekly' | 'monthly';
    target_amount: number;
    currency_id?: number | null;
    category_id?: number | null;
    is_active?: boolean;
};

export type RecurringTransactionPayload = {
    type: 'income' | 'expense';
    amount: number;
    currency_id?: number | null;
    description: string;
    frequency: 'daily' | 'weekly' | 'monthly';
    next_due_date: string;
    category_id?: number | null;
    bank_account_id?: number | null;
    debt_id?: number | null;
    payment_method: 'cash' | 'bank_account';
    is_active?: boolean;
};

export type ChartData = {
    labels: string[];
    values: number[];
    colors?: string[];
    currency_code?: string;
    currency_symbol?: string;
};

export type IncomeVsExpensesData = {
    labels: string[];
    income: number[];
    expenses: number[];
    currency_code?: string;
    currency_symbol?: string;
};

export type ExchangeRatePayload = {
    currency_id: number;
    date: string;
    rate: number;
};

export type LoyaltyCardPayload = {
    name: string;
    card_number: string;
    notes?: string | null;
    color?: string | null;
};

export type DebtPayload = {
    type: 'i_owe' | 'owed_to_me';
    person_name: string;
    description: string;
    amount: number;
    currency_id?: number | null;
    date: string;
    due_date?: string | null;
    notes?: string | null;
    status?: 'active' | 'settled';
};

export type DebtFilters = {
    type?: 'i_owe' | 'owed_to_me';
    status?: 'active' | 'settled' | 'overdue';
    search?: string;
};
