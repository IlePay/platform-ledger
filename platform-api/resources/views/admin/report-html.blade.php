<!DOCTYPE html>
<html>
<head>
    <title>Revenue Report - {{ $report->report_date }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        h1 { color: #2D4B9E; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2D4B9E; color: white; }
    </style>
</head>
<body>
    <h1>IlePay - Revenue Report</h1>
    <p><strong>Date:</strong> {{ $report->report_date->format('d/m/Y') }}</p>
    <p><strong>Période:</strong> {{ $report->period_type }}</p>
    
    <table>
        <tr>
            <th>Métrique</th>
            <th>Valeur</th>
        </tr>
        <tr>
            <td>GMV Total</td>
            <td>{{ number_format($report->total_gmv, 0, ',', ' ') }} XAF</td>
        </tr>
        <tr>
            <td>Commission (1.5%)</td>
            <td>{{ number_format($report->total_commission, 0, ',', ' ') }} XAF</td>
        </tr>
        <tr>
            <td>Transactions</td>
            <td>{{ $report->transaction_count }}</td>
        </tr>
        <tr>
            <td>Nouveaux utilisateurs</td>
            <td>{{ $report->new_users_count }}</td>
        </tr>
        <tr>
            <td>Marchands actifs</td>
            <td>{{ $report->active_merchants }}</td>
        </tr>
    </table>
    
    <p style="margin-top: 40px; color: #666; font-size: 12px;">
        Généré le {{ now()->format('d/m/Y H:i') }}
    </p>
</body>
</html>
