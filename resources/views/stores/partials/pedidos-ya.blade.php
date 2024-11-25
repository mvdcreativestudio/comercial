<!-- Integración Pedidos Ya Envíos -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/peya-logo.png') }}"
                    alt="Pedidos Ya Envíos Logo" class="img-fluid" style="width: 80px;">
            </div>
            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($store->accepts_peya_envios)
            <span
                class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center">
            <h3 class="card-title mb-1 me-2">Pedidos Ya Envíos</h3>
            <small class="d-block mb-2">Ofrece envíos a través de Pedidos Ya</small>
            <div class="form-check form-switch d-flex justify-content-center">
                <!-- Campo oculto para asegurar que se envíe el valor '0' si el checkbox no está marcado -->
                <input type="hidden" name="accepts_peya_envios" value="0">
                <input class="form-check-input" type="checkbox" id="peyaEnviosSwitch"
                    name="accepts_peya_envios" value="1" {{ $store->accepts_peya_envios ?
                'checked' : '' }}>
            </div>
            <!-- Campos Pedidos Ya (ocultos por defecto) -->
            <div id="peyaEnviosFields" class="integration-fields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label mt-2" for="peyaEnviosKey">API Key de Pedidos
                        Ya Envíos</label>
                    <input type="text" class="form-control" id="peyaEnviosKey"
                        name="peya_envios_key" placeholder="API Key de Pedidos Ya"
                        value="{{ $store->peya_envios_key ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>
