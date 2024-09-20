@extends('layouts/layoutMaster')

@section('title', 'Editar Tienda')

@section('page-script')
@vite([
  'resources/assets/js/edit-store.js'
])
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete" async defer></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Tiendas /</span> Editar Tienda
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
  <div class="alert alert-danger">
    {{ $error }}
  </div>
@endforeach
@endif

<div class="app-ecommerce">
    <!-- Formulario para editar tienda -->
    <form action="{{ route('stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información de la Tienda</h5>
                </div>
                <div class="card-body">
                    <!-- Campos del formulario -->
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label" for="store-name">Nombre</label>
                        <input type="text" class="form-control" id="store-name" name="name" required placeholder="Nombre de la tienda" value="{{ $store->name }}">
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label" for="store-address">Dirección</label>
                        <input type="text" class="form-control" id="store-address" name="address" placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation" autocomplete="off" value="{{ $store->address }}">
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label" for="store-email">Email</label>
                        <input type="email" class="form-control" id="store-email" name="email" required placeholder="Email de la tienda" value="{{ $store->email }}">
                    </div>

                    <!-- RUT -->
                    <div class="mb-3">
                        <label class="form-label" for="store-rut">RUT</label>
                        <input type="text" class="form-control" id="store-rut" name="rut" required placeholder="RUT de la tienda" value="{{ $store->rut }}">
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label" for="store-status">Estado</label>
                        <select class="form-select" id="store-status" name="status" required>
                            <option value="1" {{ $store->status == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $store->status == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <!-- Tarjetas de Integraciones -->
                    <div class="row">
                        <!-- Integración Ecommerce -->
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="card position-relative border">
                                <div class="card-header text-center bg-light">
                                    <div class="border-0 rounded-circle mx-auto">
                                        <img src="{{ asset('assets/img/integrations/ecommerce-logo.png') }}" alt="E-Commerce Logo" class="img-fluid" style="width: 80px;">
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <h3 class="card-title mb-1 me-2">E-Commerce</h3>
                                    <small class="d-block mb-2">Activa la venta en línea para tu tienda</small>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="ecommerce" value="0">
                                        <input class="form-check-input" type="checkbox" id="ecommerceSwitch" name="ecommerce" value="1" {{ $store->ecommerce ? 'checked' : '' }}>
                                    </div>
                                </div>
                                @if ($store->ecommerce)
                                  <span class="check-circle-integrations position-absolute top-0 end-0 m-2">
                                      <i class="bx bx-check text-white"></i>
                                  </span>
                                @endif
                            </div>
                        </div>

                        <!-- Integración MercadoPago -->
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="card position-relative border">
                                <div class="card-header text-center bg-light">
                                    <div class="border-0 rounded-circle mx-auto">
                                        <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}" alt="MercadoPago Logo" class="img-fluid" style="width: 80px;">
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <h3 class="card-title mb-1 me-2">MercadoPago</h3>
                                    <small class="d-block mb-2">Acepta pagos a través de MercadoPago en tu E-Commerce</small>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="accepts_mercadopago" value="0">
                                        <input class="form-check-input" type="checkbox" id="mercadoPagoSwitch" name="accepts_mercadopago" value="1" {{ $store->mercadoPagoAccount ? 'checked' : '' }}>
                                    </div>
                                    <!-- Campos MercadoPago (ocultos por defecto) -->
                                    <div id="mercadoPagoFields" class="integration-fields" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label" for="mercadoPagoPublicKey">Public Key</label>
                                            <input type="text" class="form-control" id="mercadoPagoPublicKey" name="mercadoPagoPublicKey" placeholder="Public Key de MercadoPago" value="{{ $store->mercadoPagoAccount->public_key ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="mercadoPagoAccessToken">Access Token</label>
                                            <input type="text" class="form-control" id="mercadoPagoAccessToken" name="mercadoPagoAccessToken" placeholder="Access Token de MercadoPago" value="{{ $store->mercadoPagoAccount->access_token ?? '' }}">
                                        </div>
                                        <div class="mb-3">
                                          <label class="form-label" for="mercadoPagoSecretKey">Access Token</label>
                                          <input type="text" class="form-control" id="mercadoPagoSecretKey" name="mercadoPagoSecretKey" placeholder="Secret Key de MercadoPago" value="{{ $store->mercadoPagoAccount->secret_key ?? '' }}">
                                      </div>
                                    </div>
                                </div>
                                @if ($store->mercadoPagoAccount)
                                  <span class="check-circle-integrations position-absolute top-0 end-0 m-2">
                                    <i class="bx bx-check text-white"></i>
                                  </span>
                                @endif
                            </div>
                        </div>

                        <!-- Integración Pedidos Ya Envíos -->
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="card position-relative border">
                                <div class="card-header text-center bg-light">
                                    <div class="border-0 rounded-circle mx-auto">
                                        <img src="{{ asset('assets/img/integrations/peya-logo.png') }}" alt="Pedidos Ya Envíos Logo" class="img-fluid" style="width: 80px;">
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <h3 class="card-title mb-1 me-2">Pedidos Ya Envíos</h3>
                                    <small class="d-block mb-2">Ofrece envíos a través de Pedidos Ya</small>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input type="hidden" name="accepts_peya_envios" value="0">
                                        <input class="form-check-input" type="checkbox" id="peyaEnviosSwitch" name="accepts_peya_envios" value="1" {{ $store->accepts_peya_envios ? 'checked' : '' }}>
                                    </div>
                                    <!-- Campos Pedidos Ya (ocultos por defecto) -->
                                    <div id="peyaEnviosFields" class="integration-fields" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label" for="peyaEnviosKey">API Key de Pedidos Ya Envíos</label>
                                            <input type="text" class="form-control" id="peyaEnviosKey" name="peya_envios_key" placeholder="API Key de Pedidos Ya" value="{{ $store->peya_envios_key ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @if ($store->accepts_peya_envios)
                                  <span class="check-circle-integrations position-absolute top-0 end-0 m-2">
                                    <i class="bx bx-check text-white"></i>
                                  </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger">
                                {{ $error }}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            <!-- Botones -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Actualizar Tienda</button>
            </div>
        </div>
    </div>
    </form>
</div>

<script>
  let autocomplete;

  function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('store-address'), {
      types: ['geocode']
    });
    autocomplete.setFields(['address_component']);
  }

  function geolocate() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
        const geolocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        const circle = new google.maps.Circle({
          center: geolocation,
          radius: position.coords.accuracy
        });
        autocomplete.setBounds(circle.getBounds());
      });
    }
  }
</script>
@endsection
