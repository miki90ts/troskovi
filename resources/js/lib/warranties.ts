import { t } from '@/lib/i18n';

export function daysUntilWarrantyExpiry(
    expiresAt: string | null,
): number | null {
    if (!expiresAt) {
        return null;
    }

    const now = new Date();
    const expires = new Date(expiresAt);

    return Math.ceil(
        (expires.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
    );
}

export function getWarrantyStatusClass(expiresAt: string | null): string {
    const days = daysUntilWarrantyExpiry(expiresAt);

    if (days === null) {
        return 'text-muted-foreground';
    }
    if (days < 0) {
        return 'text-destructive';
    }
    if (days <= 30) {
        return 'text-orange-500';
    }
    if (days <= 180) {
        return 'text-yellow-500';
    }

    return 'text-emerald-500';
}

export function getWarrantyStatusBadge(expiresAt: string | null): {
    label: string;
    class: string;
} {
    const days = daysUntilWarrantyExpiry(expiresAt);

    if (days === null || days < 0) {
        return {
            label: t('finance.warranties.statusExpired'),
            class: 'border-destructive/20 bg-destructive/10 text-destructive',
        };
    }
    if (days <= 30) {
        return {
            label: t('finance.warranties.statusExpiringSoon'),
            class: 'border-orange-500/20 bg-orange-500/10 text-orange-600',
        };
    }

    return {
        label: t('finance.warranties.statusActive'),
        class: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-600',
    };
}

export function formatWarrantyDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('sr-RS', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

export function formatWarrantyDaysRemaining(expiresAt: string | null): string {
    const days = daysUntilWarrantyExpiry(expiresAt);

    if (days === null) {
        return '';
    }
    if (days < 0) {
        return t('finance.warranties.daysOverdue', { count: Math.abs(days) });
    }

    return t('finance.warranties.daysRemaining', { count: days });
}

export function getReceiptPreviewUrl(receiptUrl: string): string {
    return `${receiptUrl}${receiptUrl.includes('?') ? '&' : '?'}preview=1`;
}
