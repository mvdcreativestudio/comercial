@extends('layouts/layoutMaster')

@section('title', 'Ver Lista de Precios')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">

        <div class="d-flex flex-column justify-content-center">
            <h4 class="mb-0 page-title">
            <i class="bx bx-list-ul me-2"></i> {{ $priceList->name }}
            </h4>
        </div>

        <div>
            <a href="{{ route('price-lists.edit', $priceList->id) }}" class="btn btn-primary">
                <i class="bx bx-edit-alt"></i> Editar
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <p class="form-control-plaintext">{{ $priceList->name }}</p>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <p class="form-control-plaintext">{{ $priceList->description ?: 'Sin descripción' }}</p>
            </div>
            <div class="mb-3">
                <label for="store" class="form-label">Tienda</label>
                <p class="form-control-plaintext">{{ $priceList->store->name }}</p>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <!-- Sección para mostrar los productos y sus precios -->
            <div class="mb-3">
                <label for="products" class="form-label">Productos</label>
                <table class="table table-striped" id="productsTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="productsList">
                        <!-- Aquí se cargarán los productos con sus precios -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
  // Cargar productos con AJAX al cargar la página
  $(document).ready(function() {
    $.ajax({
        url: "{{ route('price-lists.products', [$priceList->store_id, $priceList->id]) }}",
        method: 'GET',
        success: function(response) {
            var productsList = $('#productsList');
            productsList.empty();
            response.products.forEach(function(product) {
                var priceValue = product.price ? parseFloat(product.price).toFixed(2) : 'No asignado';  // Mostrar el precio si existe, de lo contrario mostrar 'No asignado'
                productsList.append(`
                    <tr>
                        <td>${product.name}</td>
                        <td>${priceValue}</td>
                    </tr>
                `);
            });
        },
        error: function(xhr) {
            console.log('Error al cargar productos:', xhr);
        }
    });
});
</script>
@endsection
