@extends('layouts/layoutMaster')

@section('title', 'Editar Producto Compuesto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/katex.scss',
'resources/assets/vendor/libs/quill/editor.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/dropzone/dropzone.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/tagify/tagify.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/quill/katex.js',
'resources/assets/vendor/libs/quill/quill.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/dropzone/dropzone.js',
'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/tagify/tagify.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/composite-products/app-composite-product-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">E-Commerce /</span><span> Editar Producto Compuesto</span>
</h4>

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="app-ecommerce" data-products='@json($products)'>

  <!-- Edit Composite Product -->
  <form action="{{ route('composite-products.update', $compositeProduct->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

      <!-- Título de la página y botones de acciones -->
      <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">Editar producto compuesto</h4>
      </div>
      <div class="d-flex align-content-center flex-wrap gap-3">
        <button type="button" class="btn btn-label-secondary" id="discardButton">Descartar</button>
        <button type="submit" name="action" value="publish" class="btn btn-primary">Guardar cambios</button>
      </div>
    </div>

    <!-- Primera fila con dos columnas de igual altura -->
    <div class="row gx-3">
      <!-- Primera columna (Información del producto) -->
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Información del Producto Compuesto</h5>
          </div>
          <div class="card-body">
            <!-- Campo para el título del producto compuesto -->
            <div class="mb-3">
              <label class="form-label" for="composite-product-name">Título</label>
              <input type="text" class="form-control" id="composite-product-name"
                placeholder="Nombre del producto compuesto" name="title" aria-label="Nombre del producto compuesto"
                value="{{ $compositeProduct->title }}" required>
            </div>
            <!-- Campo para la descripción del producto compuesto -->
            <div class="mb-3">
              <label class="form-label" for="description">Descripción</label>
              <textarea class="form-control" id="description" name="description"
                placeholder="Descripción del producto compuesto">{{ $compositeProduct->description }}</textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Segunda columna (Precios) -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Precios</h5>
          </div>
          <div class="card-body">
            <!-- Campo para el precio final -->
            <div class="mb-3">
              <label class="form-label" for="price">Precio Final</label>
              <input type="number" class="form-control" id="price" placeholder="Precio del producto compuesto"
                name="price" value="{{ $compositeProduct->price }}">
            </div>
            <!-- Campo para el precio recomendado -->
            <div class="mb-3">
              <label class="form-label" for="recommended_price">Precio Recomendado</label>
              <input type="number" class="form-control" id="recommended_price"
                placeholder="Precio recomendado (calculado automáticamente)" name="recommended_price"
                value="{{ $compositeProduct->recommended_price }}" disabled>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Segunda fila con dos columnas de igual altura -->
    <div class="row gx-3 mt-4">
      <!-- Primera columna (Productos incluidos) -->
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Productos Incluidos</h5>
          </div>
          <div class="card-body">
            <!-- Campo para seleccionar productos incluidos -->
            <div class="mb-3">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="product_ids">Productos</label>
              </div>
              <select class="select2 form-select" id="product_ids" name="product_ids[]" multiple="multiple" required>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" {{ in_array($product->id, $compositeProduct->product_ids) ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Segunda columna (Organizar) -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Organizar</h5>
          </div>
          <div class="card-body">
            <!-- Campo para seleccionar la tienda -->
            <div class="mb-3">
              <label class="form-label" for="store_id">Tienda</label>
              <select id="store_id" class="form-select" data-placeholder="Seleccione la tienda" name="store_id"
                required>
                <option value="" selected disabled>Seleccione una tienda</option>
                @foreach ($stores as $store)
                <option value="{{ $store->id }}" {{ $compositeProduct->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Sección para subir imágenes (comentada) --}}
    {{-- <div class="row gx-3 mt-4">
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Imagen del Producto Compuesto</h5>
          </div>
          <div class="card-body">
            <div class="dropzone dz-clickable" id="dropzone">
              <div class="dz-message needsclick">
                <p class="fs-4 note needsclick my-2">Arrastre la imagen aquí</p>
                <small class="text-muted d-block fs-6 my-2">o</small>
                <span class="note needsclick btn bg-label-primary d-inline" id="btnBrowse">Buscar imagen</span>
              </div>
            </div>
            <input type="file" name="image" id="compositeProductImage" class="d-none">
          </div>
        </div>
      </div>
    </div> --}}
  </form>
</div>
@endsection
