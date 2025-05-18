<!DOCTYPE html>
<html>
<head>
    <title>Daily Sales Report</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Daily Sales Report - {{ $date }}</h2>
    <p><strong>Total Revenue:</strong> {{ $totalRevenue }} Birr</p>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Sold At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->product_id }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>{{ $sale->price }} Birr</td>
                    <td>{{ \Carbon\Carbon::parse($sale->sold_at)->format('F d, Y h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Low Stock Products</h3>
    @if ($lowStockProducts->count())
        <ul>
            @foreach ($lowStockProducts as $product)
                <li>{{$product->name}} - QTY: {{ $product->quantity }}</li>
            @endforeach
        </ul>
    @else
        <p>No low stock products 🎉</p>
    @endif

    <h3>Expiring Soon</h3>
    <ul>
        @foreach($expiringSoon as $product)
            <li>{{$product->name}} - Expires on {{ \Carbon\Carbon::parse($product->expiry_date)->format('F m, Y')  }}</li>
        @endforeach
    </ul>
</body>
</html>
