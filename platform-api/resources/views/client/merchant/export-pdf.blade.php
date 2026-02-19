<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de transactions - {{ $merchant->business_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2D4B9E;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2D4B9E;
            margin-bottom: 10px;
        }
        .merchant-info {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #F9B233;
        }
        .merchant-info h2 {
            margin: 0 0 10px 0;
            color: #2D4B9E;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #2D4B9E;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .total {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 5px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/ILEPAYHD.png') }}" alt="IlePay" style="height: 100px; margin: 0 auto 10px;">
        <p style="font-size: 18px; font-weight: bold; margin: 10px 0;">Rapport de transactions</p>
        <p><strong>{{ $period }}</strong> • Généré le {{ $date }}</p>
    </div>

    <div class="merchant-info">
        <h2>{{ $merchant->business_name }}</h2>
        <p><strong>Type :</strong> {{ ucfirst(strtolower($merchant->business_type)) }}</p>
        <p><strong>QR Code :</strong> {{ $merchant->qr_code }}</p>
        <p><strong>Téléphone :</strong> {{ $merchant->phone }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Description</th>
                <th style="text-align: right;">Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $tx->fromUser ? $tx->fromUser->full_name : 'Inconnu' }}</td>
                <td>{{ $tx->description ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($tx->amount, 0, ',', ' ') }} XAF</td>
                <td>
                    @if($tx->isRefunded())
                        Remboursé
                    @else
                        {{ $tx->status }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">Aucune transaction</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        <p>Total des transactions : <span style="color: #2D4B9E;">{{ number_format($total, 0, ',', ' ') }} XAF</span></p>
        <p>Nombre de transactions : {{ $transactions->count() }}</p>
    </div>

    <div class="footer">
        <p>IlePay - Plateforme de paiement mobile</p>
        <p>Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
