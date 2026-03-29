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
    category_id?: number | null;
    is_active?: boolean;
};

export type ChartData = {
    labels: string[];
    values: number[];
    colors?: string[];
};

export type IncomeVsExpensesData = {
    labels: string[];
    income: number[];
    expenses: number[];
};

export type LoyaltyCardPayload = {
    name: string;
    card_number: string;
    notes?: string | null;
    color?: string | null;
};
