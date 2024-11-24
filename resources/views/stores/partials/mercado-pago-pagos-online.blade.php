<!-- Tarjeta para pagos online -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}"
                    alt="MercadoPago Logo" class="img-fluid" style="width: 80px;">
            </div>
            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($mercadoPagoOnline !== null)
            <span
                class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center">
            <h3 class="card-title mb-1 me-2">MercadoPago - Pagos Online</h3>
            <small class="d-block mb-2">Acepta pagos en línea a través de MercadoPago en tu
                E-Commerce</small>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_mercadopago_online" value="0">
                <input class="form-check-input" type="checkbox" id="mercadoPagoSwitchOnline"
                    name="accepts_mercadopago_online" value="1" {{ $mercadoPagoOnline
                    !==null ? 'checked' : '' }}>
            </div>
            <div id="mercadoPagoFieldsOnline" class="integration-fields"
                style="display: none;">
                <div class="mb-3">
                    <label class="form-label mt-2" for="mercadoPagoPublicKeyOnline">Public
                        Key</label>
                    <input type="text" class="form-control" id="mercadoPagoPublicKeyOnline"
                        name="mercadoPagoPublicKeyOnline"
                        placeholder="Public Key de MercadoPago"
                        value="{{ $mercadoPagoOnline->public_key ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoAccessTokenOnline">Access
                        Token</label>
                    <input type="text" class="form-control"
                        id="mercadoPagoAccessTokenOnline"
                        name="mercadoPagoAccessTokenOnline"
                        placeholder="Access Token de MercadoPago"
                        value="{{ $mercadoPagoOnline->access_token ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mercadoPagoSecretKeyOnline">Secret Key</label>
                    <input type="text" class="form-control" id="mercadoPagoSecretKeyOnline"
                        name="mercadoPagoSecretKeyOnline" placeholder="Secret Key de MercadoPago"
                        value="{{ $mercadoPagoOnline->secret_key ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>