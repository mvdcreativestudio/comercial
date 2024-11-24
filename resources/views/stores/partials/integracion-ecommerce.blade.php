<!-- Integración Ecommerce -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/ecommerce-logo.png') }}"
                    alt="E-Commerce Logo" class="img-fluid" style="width: 80px;">
            </div>
            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($store->ecommerce)
            <span
                class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif

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
    </div>
</div>