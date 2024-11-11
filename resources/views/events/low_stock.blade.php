<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Bajo Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            color: #d9534f;
            margin-bottom: 20px;
        }
        .details {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="header">⚠️ Alerta de Bajo Stock ⚠️</div>

        <p class="details">Estimado administrador,</p>

        <p class="details">El producto <strong>{{ $data['product']->name }}</strong> ha alcanzado el límite de stock definido.</p>

        <p class="details">Detalles del producto:</p>
        <ul>
            <li><strong>ID del Producto:</strong> {{ $data['product']->id }}</li>
            <li><strong>Nombre:</strong> {{ $data['product']->name }}</li>
            <li><strong>Stock Actual:</strong> {{ $data['currentStock'] }}</li>
            <li><strong>Margen de Seguridad:</strong> {{ $data['product']->safety_margin }}</li>
        </ul>

        <p class="details">Por favor, tome las medidas necesarias para reabastecer el inventario de este producto.</p>
    </div>
</body>
</html>
