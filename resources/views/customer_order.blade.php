<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Customer Order</title>
</head>
<body>
    <h1>Customer Order Details</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderData as $item)
                <tr>
                    <td>{{ $item['product_id'] }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
