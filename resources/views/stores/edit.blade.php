@extends('layouts/layoutMaster')

@section('title', 'Editar Empresa')

@section('page-script')
@vite([
'resources/assets/js/edit-store.js'
])
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete"
    async defer></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Empresas /</span> Editar Empresa
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
    <!-- Formulario para editar Empresa -->
    <form action="{{ route('stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información de la Empresa</h5>
                    </div>
                    <div class="card-body">
                        <!-- Campos del formulario -->
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label class="form-label" for="store-name">Nombre</label>
                            <input type="text" class="form-control" id="store-name" name="name" required
                                placeholder="Nombre de la Empresa" value="{{ $store->name }}">
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label class="form-label" for="store-address">Dirección</label>
                            <input type="text" class="form-control" id="store-address" name="address"
                                placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation"
                                autocomplete="off" value="{{ $store->address }}">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label" for="store-email">Email</label>
                            <input type="email" class="form-control" id="store-email" name="email" required
                                placeholder="Email de la Empresa" value="{{ $store->email }}">
                        </div>

                        <!-- RUT -->
                        <div class="mb-3">
                            <label class="form-label" for="store-rut">RUT</label>
                            <input type="text" class="form-control" id="store-rut" name="rut" required
                                placeholder="RUT de la Empresa" value="{{ $store->rut }}">
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
                                            <img src="{{ asset('assets/img/integrations/ecommerce-logo.png') }}"
                                                alt="E-Commerce Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">E-Commerce</h3>
                                        <small class="d-block mb-2">Activa la venta en línea para tu tienda</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="hidden" name="ecommerce" value="0">
                                            <input class="form-check-input" type="checkbox" id="ecommerceSwitch"
                                                name="ecommerce" value="1" {{ $store->ecommerce ? 'checked' : '' }}>
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
                                            <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}"
                                                alt="MercadoPago Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">MercadoPago</h3>
                                        <small class="d-block mb-2">Acepta pagos a través de MercadoPago en tu
                                            E-Commerce</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="hidden" name="accepts_mercadopago" value="0">
                                            <input class="form-check-input" type="checkbox" id="mercadoPagoSwitch"
                                                name="accepts_mercadopago" value="1" {{ $store->mercadoPagoAccount ?
                                            'checked' : '' }}>
                                        </div>
                                        <!-- Campos MercadoPago (ocultos por defecto) -->
                                        <div id="mercadoPagoFields" class="integration-fields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label" for="mercadoPagoPublicKey">Public Key</label>
                                                <input type="text" class="form-control" id="mercadoPagoPublicKey"
                                                    name="mercadoPagoPublicKey" placeholder="Public Key de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->public_key ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="mercadoPagoAccessToken">Access
                                                    Token</label>
                                                <input type="text" class="form-control" id="mercadoPagoAccessToken"
                                                    name="mercadoPagoAccessToken"
                                                    placeholder="Access Token de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->access_token ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="mercadoPagoSecretKey">Access
                                                    Token</label>
                                                <input type="text" class="form-control" id="mercadoPagoSecretKey"
                                                    name="mercadoPagoSecretKey" placeholder="Secret Key de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->secret_key ?? '' }}">
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
                                            <img src="{{ asset('assets/img/integrations/peya-logo.png') }}"
                                                alt="Pedidos Ya Envíos Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">Pedidos Ya Envíos</h3>
                                        <small class="d-block mb-2">Ofrece envíos a través de Pedidos Ya</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="hidden" name="accepts_peya_envios" value="0">
                                            <input class="form-check-input" type="checkbox" id="peyaEnviosSwitch"
                                                name="accepts_peya_envios" value="1" {{ $store->accepts_peya_envios ?
                                            'checked' : '' }}>
                                        </div>
                                        <!-- Campos Pedidos Ya (ocultos por defecto) -->
                                        <div id="peyaEnviosFields" class="integration-fields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label" for="peyaEnviosKey">API Key de Pedidos Ya
                                                    Envíos</label>
                                                <input type="text" class="form-control" id="peyaEnviosKey"
                                                    name="peya_envios_key" placeholder="API Key de Pedidos Ya"
                                                    value="{{ $store->peya_envios_key ?? '' }}">
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

                        <!-- Acepta MercadoPago Switch -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <!-- Campo oculto para asegurar que un valor falso se envíe si el checkbox no está marcado -->
                                <input type="hidden" name="accepts_mercadopago" value="0">
                                <input class="form-check-input" type="checkbox" id="mercadoPagoSwitch"
                                    name="accepts_mercadopago" value="1" {{ $store->mercadoPagoAccount ? 'checked' : ''
                                }}>
                                <label class="form-check-label" for="mercadoPagoSwitch">Acepta MercadoPago</label>
                            </div>
                        </div>



                        <!-- Campos MercadoPago (ocultos por defecto) -->
                        <div id="mercadoPagoFields" style="display: none;">
                            <!-- Public Key -->
                            <div class="mb-3">
                                <label class="form-label" for="mercadoPagoPublicKey">Public Key</label>
                                <input type="text" class="form-control" id="mercadoPagoPublicKey"
                                    name="mercadoPagoPublicKey" placeholder="Public Key de MercadoPago"
                                    value="{{ $store->mercadoPagoAccount->public_key ?? '' }}">
                            </div>

                            <!-- Access Token -->
                            <div class="mb-3">
                                <label class="form-label" for="mercadoPagoAccessToken">Access Token</label>
                                <input type="text" class="form-control" id="mercadoPagoAccessToken"
                                    name="mercadoPagoAccessToken" placeholder="Access Token de MercadoPago"
                                    value="{{ $store->mercadoPagoAccount->access_token ?? '' }}">
                            </div>

                            <!-- Secret Key -->
                            <div class="mb-3">
                                <label class="form-label" for="mercadoPagoSecretKey">Clave Secreta</label>
                                <input type="text" class="form-control" id="mercadoPagoSecreyKey"
                                    name="mercadoPagoSecretKey" placeholder="Clave secreta de MercadoPago"
                                    value="{{ $store->mercadoPagoAccount->secret_key ?? ''}}">
                            </div>
                        </div>


                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <!-- Campo oculto para asegurar que un valor falso se envíe si el checkbox no está marcado -->
                                <input type="hidden" name="invoices_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="invoicesEnabledSwitch"
                                    name="invoices_enabled" value="1" {{ $store->invoices_enabled ? 'checked' : '' }}>
                                <label class="form-check-label" for="invoicesEnabledSwitch">Habilitar
                                    Facturación</label>
                            </div>
                        </div>

                        @if($store->pymo_user && $store->pymo_password && $store->pymo_branch_office &&
                        !empty($companyInfo))
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="automatic_billing" value="0">
                                <input class="form-check-input" type="checkbox" id="automaticBillingSwitch"
                                    name="automatic_billing" value="1" {{ $store->automatic_billing ? 'checked' : '' }}>
                                <label class="form-check-label" for="automaticBillingSwitch">Habilitar Facturación
                                    Automática</label>
                            </div>
                        </div>
                        @endif

                        <!-- Campos de Configuración de PyMo (ocultos por defecto) -->
                        <div id="pymoFields" style="display: {{ $store->invoices_enabled ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label" for="pymoUser">Usuario PyMo</label>
                                <input type="text" class="form-control" id="pymoUser" name="pymo_user"
                                    placeholder="Usuario PyMo" value="{{ $store->pymo_user }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="pymoPassword">Contraseña PyMo</label>
                                <input type="password" class="form-control" id="pymoPassword" name="pymo_password"
                                    placeholder="Contraseña PyMo" value="{{ $store->pymo_password }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="pymoBranchOffice">Sucursal PyMo</label>
                                <input type="text" class="form-control" id="pymoBranchOffice" name="pymo_branch_office"
                                    placeholder="Sucursal PyMo" value="{{ $store->pymo_branch_office }}">
                            </div>
                        </div>

                        @if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password &&
                        !empty($companyInfo))
                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Logo de la empresa en Pymo</h4>
                                </div>
                                <div class="card-body">
                                    @if($logoUrl)
                                    <div class="mb-3">
                                        <img src="{{ asset($logoUrl) }}" alt="Company Logo" class="img-thumbnail"
                                            style="max-width: 200px;">
                                    </div>
                                    @endif
                                    <form action="{{ route('accounting.uploadLogo') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group">
                                            <input type="file" class="form-control-file" id="logo" name="logo">
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">Actualizar Logo</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Información de la empresa en Pymo</h4>
                                </div>
                                <div class="card-body">
                                    <form>
                                        @if(!empty($companyInfo['name']))
                                        <div class="form-group">
                                            <label for="companyName">Nombre de la Empresa</label>
                                            <input type="text" class="form-control my-3" id="companyName"
                                                value="{{ $companyInfo['name'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['rut']))
                                        <div class="form-group">
                                            <label for="companyRUT">RUT</label>
                                            <input type="text" class="form-control my-3" id="companyRUT"
                                                value="{{ $companyInfo['rut'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['socialPurpose']))
                                        <div class="form-group">
                                            <label for="socialPurpose">Propósito Social</label>
                                            <input type="text" class="form-control my-3" id="socialPurpose"
                                                value="{{ $companyInfo['socialPurpose'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['resolutionNumber']))
                                        <div class="form-group">
                                            <label for="resolutionNumber">Número de Resolución</label>
                                            <input type="text" class="form-control my-3" id="resolutionNumber"
                                                value="{{ $companyInfo['resolutionNumber'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['email']))
                                        <div class="form-group">
                                            <label for="companyEmail">Correo Electrónico</label>
                                            <input type="email" class="form-control my-3" id="companyEmail"
                                                value="{{ $companyInfo['email'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['createdAt']))
                                        <div class="form-group">
                                            <label for="createdAt">Fecha de Creación</label>
                                            <input type="text" class="form-control my-3" id="createdAt"
                                                value="{{ $companyInfo['createdAt'] }}" disabled>
                                        </div>
                                        @endif

                                        @if(!empty($companyInfo['updatedAt']))
                                        <div class="form-group">
                                            <label for="updatedAt">Fecha de Actualización</label>
                                            <input type="text" class="form-control my-3" id="updatedAt"
                                                value="{{ $companyInfo['updatedAt'] }}" disabled>
                                        </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

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
                    <button type="submit" class="btn btn-primary">Actualizar Empresa</button>
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