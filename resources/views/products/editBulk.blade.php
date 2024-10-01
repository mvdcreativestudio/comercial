@extends('layouts/layoutMaster')

@section('content')
<div class="container-fluid mt-5">
    <h1 class="text-center mb-4">Editar Productos en Masa</h1>
    <form action="{{ route('products.updateBulk') }}" method="POST">
        @csrf
        @foreach($products as $product)
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap">
                    <div class="form-group mb-3 me-3 flex-fill">
                        <label for="name_{{ $product->id }}" class="form-label">Nombre</label>
                        <input type="text" name="products[{{ $product->id }}][name]" value="{{ $product->name }}" class="form-control" id="name_{{ $product->id }}">
                    </div>
                    <div class="form-group mb-3 me-3 flex-fill">
                        <label for="old_price_{{ $product->id }}" class="form-label">Precio Normal</label>
                        <input type="text" name="products[{{ $product->id }}][old_price]" value="{{ $product->old_price }}" class="form-control" id="old_price_{{ $product->id }}">
                    </div>
                    <div class="form-group mb-3 flex-fill">
                        <label for="price_{{ $product->id }}" class="form-label">Precio Rebajado</label>
                        <input type="text" name="products[{{ $product->id }}][price]" value="{{ $product->price }}" class="form-control" id="price_{{ $product->id }}">
                    </div>
                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                </div>
            </div>
        @endforeach
        <div class="col-12 position-fixed bottom-0 mb-3 end-0 me-3">
            <div class="d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </form>
</div>
@endsection
