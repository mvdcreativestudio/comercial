@extends('layouts.layoutMaster')

@section('content')
<div class="container">
    <h2 class="my-4">Ventas Realizadas</h2>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Ventas Efectivo</th>
                <th>Ventas POS</th>
                <th>Descuento</th>
                <th>ID Cliente</th>
                <th>Total</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->date }}</td>
                <td>{{ $sale->hour }}</td>
                <td>{{ $sale->cash_sales }}</td>
                <td>{{ $sale->pos_sales }}</td>
                <td>{{ $sale->discount }}</td>
                <td>{{ $sale->client_id }}</td>
                <td>{{ $sale->total }}</td>
                <td>{{ $sale->notes }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">No se encontraron ventas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
