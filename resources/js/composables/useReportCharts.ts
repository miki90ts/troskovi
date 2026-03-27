import { t } from '@/lib/i18n';
import type { ChartData, IncomeVsExpensesData } from '@/types';

function chartTheme() {
    return {
        mode:
            typeof document !== 'undefined' &&
            document.documentElement.classList.contains('dark')
                ? ('dark' as const)
                : ('light' as const),
        palette: 'palette1',
    };
}

export function useReportCharts() {
    function barChartOptions(
        data: IncomeVsExpensesData | null,
    ): ApexCharts.ApexOptions {
        if (!data) {
            return {};
        }

        return {
            chart: {
                type: 'bar' as const,
                toolbar: { show: false },
                background: 'transparent',
            },
            theme: chartTheme(),
            plotOptions: { bar: { columnWidth: '58%', borderRadius: 10 } },
            xaxis: { categories: data.labels },
            colors: ['#14b8a6', '#f97316'],
            dataLabels: { enabled: false },
            legend: { position: 'top' as const },
            grid: { borderColor: 'var(--border)' },
        };
    }

    function barChartSeries(data: IncomeVsExpensesData | null) {
        if (!data) {
            return [];
        }

        return [
            {
                name: t('finance.reports.income'),
                data: data.income,
            },
            {
                name: t('finance.reports.expenses'),
                data: data.expenses,
            },
        ];
    }

    function lineChartOptions(data: ChartData | null): ApexCharts.ApexOptions {
        if (!data) {
            return {};
        }

        return {
            chart: {
                type: 'area' as const,
                toolbar: { show: false },
                background: 'transparent',
            },
            theme: chartTheme(),
            xaxis: { categories: data.labels },
            colors: ['#14b8a6'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' as const, width: 2 },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.4, opacityTo: 0 },
            },
            grid: { borderColor: 'var(--border)' },
        };
    }

    function lineChartSeries(data: ChartData | null) {
        if (!data) {
            return [];
        }

        return [
            {
                name: t('finance.reports.netBalance'),
                data: data.values,
            },
        ];
    }

    function donutOptions(
        data: ChartData | null,
        title: string,
    ): ApexCharts.ApexOptions {
        if (!data) {
            return {};
        }

        return {
            chart: { type: 'donut' as const, background: 'transparent' },
            theme: chartTheme(),
            labels: data.labels,
            colors: data.colors?.length ? data.colors : undefined,
            legend: { position: 'bottom' as const },
            title: {
                text: title,
                align: 'center' as const,
                style: { fontSize: '14px' },
            },
            plotOptions: { pie: { donut: { size: '65%' } } },
        };
    }

    function pieOptions(data: ChartData | null): ApexCharts.ApexOptions {
        if (!data) {
            return {};
        }

        return {
            chart: { type: 'pie' as const, background: 'transparent' },
            theme: chartTheme(),
            labels: data.labels,
            colors: ['#14b8a6', '#0f766e'],
            legend: { position: 'bottom' as const },
        };
    }

    return {
        barChartOptions,
        barChartSeries,
        lineChartOptions,
        lineChartSeries,
        donutOptions,
        pieOptions,
    };
}
