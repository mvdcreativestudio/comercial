@extends('layouts/layoutMaster')

@section('content')
    <div
        class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
        <!-- Título del formulario alineado a la izquierda -->
        <div class="d-flex flex-column justify-content-center">
            <h4 class="mb-0 page-title">
                <i class="bx bx-box me-2"></i> Agregar Productos en Masa
            </h4>
        </div>
    </div>

    <form action="{{ route('products.storeBulk') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div id="product-container">
            <div class="card mb-4 product-row shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="name_0" class="form-label">Nombre del Producto</label>
                            <input type="text" name="products[0][name]" class="form-control" id="name_0"
                                placeholder="Ingrese el nombre">
                        </div>
                        <div class="col-md-2">
                            <label for="old_price_0" class="form-label">Precio Normal</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="products[0][old_price]" class="form-control"
                                    id="old_price_0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="price_0" class="form-label">Precio Rebajado</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="products[0][price]" class="form-control"
                                    id="price_0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="stock_0" class="form-label">Stock</label>
                            <input type="number" name="products[0][stock]" class="form-control" id="stock_0" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label for="safety_margin_0" class="form-label">Margen de Seguridad</label>
                            <input type="number" name="products[0][safety_margin]" class="form-control" id="safety_margin_0" placeholder="0">
                        </div>
                        <div class="col-md-3">
                            <label for="store_id_0" class="form-label">Tienda</label>
                            <select name="products[0][store_id]" class="form-select" id="store_id_0">
                                <option value="" selected disabled>Seleccione una tienda</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <div class="">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary add-product">+ Agregar Producto</button>
                </div>
            </div>
        </div>
        <div class="col-12 position-fixed bottom-0 mb-3 end-0 me-3">
            <div class="d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-primary">Guardar Productos</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let productIndex = 1; // Inicialmente hay 1 producto

            function addProductRow() {
                const productContainer = document.getElementById('product-container');

                const newProductRow = document.createElement('div');
                newProductRow.classList.add('card', 'mb-4', 'product-row', 'shadow-sm');
                newProductRow.innerHTML = `
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="name_${productIndex}" class="form-label">Nombre del Producto</label>
                                <input type="text" name="products[${productIndex}][name]" class="form-control" id="name_${productIndex}" placeholder="Ingrese el nombre">
                            </div>
                            <div class="col-md-2">
                                <label for="old_price_${productIndex}" class="form-label">Precio Normal</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="products[${productIndex}][old_price]" class="form-control" id="old_price_${productIndex}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="price_${productIndex}" class="form-label">Precio Rebajado</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="products[${productIndex}][price]" class="form-control" id="price_${productIndex}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="stock_${productIndex}" class="form-label">Stock</label>
                                <input type="number" name="products[${productIndex}][stock]" class="form-control" id="stock_${productIndex}" placeholder="0">
                            </div>
                            <div class="col-md-2">
                                <label for="safety_margin_${productIndex}" class="form-label">Margen de Seguridad</label>
                                <input type="number" name="products[${productIndex}][safety_margin]" class="form-control" id="safety_margin_${productIndex}" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label for="store_id_${productIndex}" class="form-label">Tienda</label>
                                <select name="products[${productIndex}][store_id]" class="form-select" id="store_id_${productIndex}">
                                    ${document.querySelector('select[name="products[0][store_id]"]').innerHTML}
                                </select>
                            </div>
                        </div>
                    </div>
                `;

                productContainer.appendChild(newProductRow);
                productIndex++;
            }

            // Usar delegación de eventos para manejar clics en botones add-product
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('add-product')) {
                    addProductRow();
                }
            });
        });
    </script>
@endsection
