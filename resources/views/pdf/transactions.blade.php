<!DOCTYPE html>
<html lang="sr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $typeLabel }}</title>
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
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 20px;
            color: #0d9488;
            margin-bottom: 4px;
        }
        .header .meta {
            font-size: 9px;
            color: #6b7280;
        }
        .filters {
            background-color: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 9px;
            color: #374151;
        }
        .filters strong {
            color: #0d9488;
        }
        .summary {
            margin-bottom: 16px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 10px 14px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary .label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            display: block;
            margin-bottom: 4px;
        }
        .summary .value {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #0d9488;
            color: #ffffff;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .data-table th.amount-col {
            text-align: right;
        }
        .data-table td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .data-table .amount.expense {
            color: #dc2626;
        }
        .data-table .amount.income {
            color: #16a34a;
        }
        .data-table .notes {
            font-size: 8px;
            color: #6b7280;
            max-width: 140px;
            word-wrap: break-word;
        }
        .data-table .category-badge {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 2px 6px;
            font-size: 8px;
        }
        .data-table .muted {
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
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $typeLabel }}</h1>
        <div class="meta">Generisano: {{ $generatedAt }}</div>
    </div>

    @if(count($appliedFilters) > 0)
        <div class="filters">
            <strong>Primenjeni filteri:</strong>
            {{ implode(' | ', $appliedFilters) }}
        </div>
    @endif

    <div class="summary">
        <table>
            <tr>
                <td>
                    <span class="label">Ukupan iznos</span>
                    <span class="value">{{ number_format($totalAmount, 2, ',', '.') }} RSD</span>
                </td>
                <td>
                    <span class="label">Prosečan iznos</span>
                    <span class="value">{{ number_format($averageAmount, 2, ',', '.') }} RSD</span>
                </td>
                <td>
                    <span class="label">Broj transakcija</span>
                    <span class="value">{{ $count }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if($transactions->isEmpty())
        <div class="empty-state">
            Nema transakcija za prikaz sa izabranim filterima.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Opis</th>
                    <th>Kategorija</th>
                    @if($type === 'expense')
                        <th>Način plaćanja</th>
                    @else
                        <th>Račun</th>
                    @endif
                    <th class="amount-col">Iznos</th>
                    <th>Napomena</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td>{{ $tx->date->format('d.m.Y') }}</td>
                        <td>{{ $tx->description }}</td>
                        <td>
                            @if($tx->category)
                                <span class="category-badge">{{ $tx->category->name }}</span>
                            @else
                                <span class="muted">Bez kategorije</span>
                            @endif
                        </td>
                        @if($type === 'expense')
                            <td>{{ $tx->payment_method->value === 'cash' ? 'Keš' : 'Bankovni račun' }}</td>
                        @else
                            <td>{{ $tx->bankAccount?->name ?? '—' }}</td>
                        @endif
                        <td class="amount {{ $type }}">
                            {{ $type === 'expense' ? '-' : '' }}{{ number_format($tx->amount, 2, ',', '.') }}
                        </td>
                        <td class="notes">{{ $tx->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Strana <span class="pagenum"></span> — {{ $typeLabel }} — {{ $generatedAt }}
    </div>
</body>
</html>
