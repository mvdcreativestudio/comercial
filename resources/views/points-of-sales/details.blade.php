@extends('layouts.layoutMaster')

@section('title', 'Detalles de la Caja Registradora')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Detalles de la Caja Registradora ID: {{ $cashRegister->id }}</h1>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Hora de Apertura</th>
                    <th>Hora de Cierre</th>
                    <th>Ventas en Efectivo</th>
                    <th>Ventas POS</th>
                    <th>Flotante de Efectivo</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Ver Ventas</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($details as $detail)
                <tr>
                    <td>{{ $detail->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($detail->open_time)->format('H:i:s d/m/y') }}</td>
                    <td>{{ $detail->close_time ? \Carbon\Carbon::parse($detail->close_time)->format('H:i:s d/m/y') : 'No ha cerrado.' }}</td>
                    <td>{{ $detail->cash_sales }}</td>
                    <td>{{ $detail->pos_sales }}</td>
                    <td>{{ $detail->cash_float }}</td>
                    <td>
                        @if(is_null($detail->close_time))
                            <span class="badge bg-success">ABIERTA</span>
                        @else
                            <span class="badge bg-danger">CERRADA</span>
                        @endif
                    </td>
                    <td>{{ $detail->cash_sales + $detail->pos_sales }}</td>
                    <td>
                        <button class="btn btn-outline-primary btn-view-sales" data-id="{{ $detail->id }}">
                            &rarr;
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var baseUrl = "{{ url('/') }}";
    
    $(document).ready(function() {
        $('.btn-view-sales').click(function() {
            var detailId = $(this).data('id'); // Capturamos el detail->id
            window.location.href = baseUrl + 'admin/point-of-sale/details/sales/' + detailId; // Construye la URL completa
        });
    });
</script>
@endsection
