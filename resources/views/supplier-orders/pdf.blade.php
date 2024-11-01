<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Helvetica', sans-serif;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 0;
        }
        .table th {
            background-color: #e9ecef;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .information-title {
            background-color: #007bff;
            color: #fff;
            padding: 8px;
            text-align: center;
            margin-bottom: 10px;
            border-radius: 0.25rem;
        }
        .materials-title {
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: bold;
        }
        .table {
            margin-bottom: 30px;
        }
        tbody {
          font-size: 12px;
          margin: 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Orden de Compra #{{ $order->id }}</h1>
        </div>
        <div class="information">
            <div class="information-title">Información de la Orden</div>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Proveedor</th>
                        <td>{{ $order->supplier->name }}</td>
                    </tr>
                    <tr>
                        <th>Fecha de Orden</th>
                        <td>{{ $order->order_date }}</td>
                    </tr>
                    <tr>
                        <th>Estado de Envío</th>
                        <td>{{ ucfirst($order->shipping_status) }}</td>
                    </tr>
                    <tr>
                        <th>Empresa</th>
                        <td>{{ $order->store->name }}</td>
                    </tr>
                    <tr>
                        <th>Notas</th>
                        <td>{{ $order->notes ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="materials">
            <div class="materials-title">Materias Primas Solicitadas</div>
            <table class="table table-bordered">
                <thead class="table-light text-center">
                    <tr>
                        <th>Materia Prima</th>
                        <th>Cantidad</th>
                        <th>Costo por Unidad</th>
                        <th>Total</th>

                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($order->rawMaterials as $material)
                        <tr>
                            <td>{{ $material->name }}</td>
                            <td>{{ $material->pivot->quantity }}{{$material->unit_of_measure}}</td>
                            <td>${{ number_format($material->pivot->unit_cost, 0) }}</td>
                            <td>${{ number_format($material->pivot->total_cost,0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            <p>Este es un documento generado automáticamente. Si tienes alguna pregunta, comunícate con nuestro equipo de soporte.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
