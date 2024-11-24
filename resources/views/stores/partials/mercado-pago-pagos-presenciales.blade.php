<!-- Tarjeta para pagos presenciales -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}" alt="MercadoPago Logo"
                    class="img-fluid" style="width: 80px;">
            </div>
            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($mercadoPagoPresencial !== null)
            <span class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center">
            <h3 class="card-title mb-1 me-2">MercadoPago - Pagos Presenciales</h3>
            <small class="d-block mb-2">Acepta pagos presenciales a través de QR dinámicos o prefijados de
                MercadoPago</small>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_mercadopago_presencial" value="0">
                <input class="form-check-input" type="checkbox" id="mercadoPagoSwitchPresencial"
                    name="accepts_mercadopago_presencial" value="1" {{ $mercadoPagoPresencial !== null ? 'checked' : '' }}>
            </div>
            <div id="mercadoPagoFieldsPresencial" class="integration-fields p-3"
                style="display: {{ $mercadoPagoPresencial !== null ? 'block' : 'none' }}; max-height: 234px; overflow-y: auto;">
                <!-- Otros campos de configuración -->
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoPublicKeyPresencial">Public Key</label>
                    <input type="text" class="form-control" id="mercadoPagoPublicKeyPresencial"
                        name="mercadoPagoPublicKeyPresencial" placeholder="Public Key de MercadoPago"
                        value="{{ $mercadoPagoPresencial->public_key ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoAccessTokenPresencial">Access Token</label>
                    <input type="text" class="form-control" id="mercadoPagoAccessTokenPresencial"
                        name="mercadoPagoAccessTokenPresencial" placeholder="Access Token de MercadoPago"
                        value="{{ $mercadoPagoPresencial->access_token ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoSecretKeyPresencial">Secret Key</label>
                    <input type="text" class="form-control" id="mercadoPagoSecretKeyPresencial"
                        name="mercadoPagoSecretKeyPresencial" placeholder="Secret Key de MercadoPago"
                        value="{{ $mercadoPagoPresencial->secret_key ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoUserIdPresencial">User ID</label>
                    <input type="text" class="form-control" id="mercadoPagoUserIdPresencial"
                        name="mercadoPagoUserIdPresencial" placeholder="User ID de MercadoPago"
                        value="{{ $mercadoPagoPresencial->user_id_mp ?? '' }}">
                </div>

                <!-- Campo de Nombre de Sucursal -->
                <div class="mb-3">
                    <label class="form-label" for="branch-name">Nombre de la Sucursal</label>
                    <input type="text" class="form-control" id="branch-name" name="branch_name" disabled
                        value="{{ $mercadoPagoAccountStore->name ?? $store->name }}">
                </div>

                <!-- Campo de External ID -->
                <div class="mb-3">
                    <label class="form-label" for="external-id">ID Externo</label>
                    <input type="text" class="form-control" id="external-id" name="external_id" disabled
                        value="{{ $mercadoPagoAccountStore->external_id ?? 'SUC' . $store->id }}">
                </div>

                <!-- Campos de ubicación -->
                <div class="mb-3">
                    <label class="form-label" for="street_number">Número</label>
                    <input type="text" class="form-control" id="street_number" name="street_number"
                        value="{{ $mercadoPagoAccountStore->street_number ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="street_name">Calle</label>
                    <input type="text" class="form-control" id="street_name" name="street_name"
                        value="{{ $mercadoPagoAccountStore->street_name ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="city_name">Ciudad</label>
                    <input type="text" class="form-control" id="city_name" name="city_name"
                        value="{{ $mercadoPagoAccountStore->city_name ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="state_name">Estado</label>
                    <input type="text" class="form-control" id="state_name" name="state_name"
                        value="{{ $mercadoPagoAccountStore->state_name ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="latitude">Latitud</label>
                    <input type="text" class="form-control" id="latitude" name="latitude" readonly
                        value="{{ $mercadoPagoAccountStore->latitude ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="longitude">Longitud</label>
                    <input type="text" class="form-control" id="longitude" name="longitude" readonly
                        value="{{ $mercadoPagoAccountStore->longitude ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="reference">Referencia</label>
                    <input type="text" class="form-control" id="reference" name="reference"
                        value="{{ $mercadoPagoAccountStore->reference ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>
