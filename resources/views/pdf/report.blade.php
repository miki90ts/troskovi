<!DOCTYPE html>
<html lang="sr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Finansijski izveštaj — {{ $periodLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #0d9488;
            margin-bottom: 2px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #374151;
        }
        .header .meta {
            font-size: 9px;
            color: #6b7280;
            margin-top: 4px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .kpi-table td {
            padding: 12px 14px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .kpi-table .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            display: block;
            margin-bottom: 4px;
        }
        .kpi-table .value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .kpi-table .value.income {
            color: #16a34a;
        }
        .kpi-table .value.expense {
            color: #dc2626;
        }
        .kpi-table .value.savings {
            color: #0d9488;
        }
        .kpi-table .sub {
            font-size: 8px;
            color: #9ca3af;
            display: block;
            margin-top: 2px;
        }
        .breakdown-section {
            margin-bottom: 24px;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .breakdown-table th {
            background-color: #0d9488;
            color: #ffffff;
            padding: 7px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .breakdown-table th.amount-col {
            text-align: right;
        }
        .breakdown-table th.percent-col {
            text-align: right;
            width: 70px;
        }
        .breakdown-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .breakdown-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .breakdown-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .breakdown-table .percent {
            text-align: right;
            color: #6b7280;
        }
        .breakdown-table .total-row td {
            border-top: 2px solid #0d9488;
            font-weight: bold;
            background-color: #f0fdfa;
        }
        .two-col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .two-col td {
            width: 48%;
            vertical-align: top;
            padding: 0;
        }
        .two-col td:first-child {
            padding-right: 12px;
        }
        .two-col td:last-child {
            padding-left: 12px;
        }
        .empty {
            text-align: center;
            padding: 16px;
            color: #9ca3af;
            font-style: italic;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            padding: 8px 0;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Finansijski izveštaj</h1>
        <div class="subtitle">{{ $periodLabel }} pregled — {{ $summary['period_start'] ? date('d.m.Y', strtotime($summary['period_start'])) : '' }} do {{ $summary['period_end'] ? date('d.m.Y', strtotime($summary['period_end'])) : '' }}</div>
        <div class="meta">Generisano: {{ $generatedAt }}</div>
    </div>

    {{-- KPI Summary --}}
    <div class="section-title">Pregled</div>
    <table class="kpi-table">
        <tr>
            <td>
                <span class="label">Ukupan prihod</span>
                <span class="value income">{{ number_format($summary['total_income'], 2, ',', '.') }} RSD</span>
            </td>
            <td>
                <span class="label">Ukupan trošak</span>
                <span class="value expense">{{ number_format($summary['total_expenses'], 2, ',', '.') }} RSD</span>
            </td>
            <td>
                <span class="label">Neto ušteda</span>
                <span class="value savings">{{ number_format($summary['net_savings'], 2, ',', '.') }} RSD</span>
            </td>
            <td>
                <span class="label">Stopa uštede</span>
                <span class="value">{{ $summary['savings_rate'] }}%</span>
                <span class="sub">Mesečna promena: {{ $summary['mom_change'] > 0 ? '+' : '' }}{{ $summary['mom_change'] }}%</span>
            </td>
        </tr>
    </table>

    {{-- Expense & Income Breakdown side by side --}}
    <table class="two-col">
        <tr>
            <td>
                <div class="breakdown-section">
                    <div class="section-title">Troškovi po kategorijama</div>
                    @if(count($expenseBreakdown['labels']) > 0)
                        @php
                            $expTotal = array_sum($expenseBreakdown['values']);
                        @endphp
                        <table class="breakdown-table">
                            <thead>
                                <tr>
                                    <th>Kategorija</th>
                                    <th class="amount-col">Iznos</th>
                                    <th class="percent-col">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenseBreakdown['labels'] as $i => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="amount">{{ number_format($expenseBreakdown['values'][$i], 2, ',', '.') }}</td>
                                        <td class="percent">{{ $expTotal > 0 ? number_format(($expenseBreakdown['values'][$i] / $expTotal) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>Ukupno</td>
                                    <td class="amount">{{ number_format($expTotal, 2, ',', '.') }}</td>
                                    <td class="percent">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="empty">Nema podataka o troškovima.</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="breakdown-section">
                    <div class="section-title">Prihodi po kategorijama</div>
                    @if(count($incomeBreakdown['labels']) > 0)
                        @php
                            $incTotal = array_sum($incomeBreakdown['values']);
                        @endphp
                        <table class="breakdown-table">
                            <thead>
                                <tr>
                                    <th>Kategorija</th>
                                    <th class="amount-col">Iznos</th>
                                    <th class="percent-col">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($incomeBreakdown['labels'] as $i => $label)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="amount">{{ number_format($incomeBreakdown['values'][$i], 2, ',', '.') }}</td>
                                        <td class="percent">{{ $incTotal > 0 ? number_format(($incomeBreakdown['values'][$i] / $incTotal) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>Ukupno</td>
                                    <td class="amount">{{ number_format($incTotal, 2, ',', '.') }}</td>
                                    <td class="percent">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="empty">Nema podataka o prihodima.</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Cash vs Bank --}}
    <div class="breakdown-section">
        <div class="section-title">Keš vs Bankovni račun (troškovi)</div>
        @php
            $cashBankTotal = array_sum($cashVsBank['values']);
        @endphp
        @if($cashBankTotal > 0)
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Način plaćanja</th>
                        <th class="amount-col">Iznos</th>
                        <th class="percent-col">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashVsBank['labels'] as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="amount">{{ number_format($cashVsBank['values'][$i], 2, ',', '.') }} RSD</td>
                            <td class="percent">{{ $cashBankTotal > 0 ? number_format(($cashVsBank['values'][$i] / $cashBankTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Ukupno</td>
                        <td class="amount">{{ number_format($cashBankTotal, 2, ',', '.') }} RSD</td>
                        <td class="percent">100%</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nema podataka o načinu plaćanja.</div>
        @endif
    </div>

    <div class="footer">
        Finansijski izveštaj — {{ $periodLabel }} — {{ $generatedAt }}
    </div>
</body>
</html>
