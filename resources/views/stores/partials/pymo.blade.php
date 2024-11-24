<!-- Integración Pymo -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/pymo-logo.png') }}"
                    alt="Pymo Logo" class="img-fluid" style="width: 80px;">
            </div>

            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($store->invoices_enabled)
            <span
                class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center">
            <h3 class="card-title mb-1 me-2">Pymo</h3>
            <small class="d-block mb-2">Facturación Electrónica a través de Pymo</small>
            <div class="form-check form-switch d-flex justify-content-center">
                <!-- Campo oculto para asegurar que se envíe el valor '0' si el checkbox no está marcado -->
                <input type="hidden" name="invoices_enabled" value="0">
                <input class="form-check-input" type="checkbox" id="invoicesEnabledSwitch"
                    name="invoices_enabled" value="1" {{ $store->invoices_enabled ?
                'checked' : '' }}>
            </div>

            @if ($store->invoices_enabled == 0)
            <div class="mt-4">
                <small class="">¿Aún no tienes cuenta? <a href="https://pymo.uy/"
                        target="_blank">Registrate
                        aquí</a></small>
            </div>
            @endif


            <!-- Campos de Configuración de PyMo (ocultos por defecto) -->
            <div id="pymoFields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label mt-2" for="pymoUser">Usuario PyMo</label>
                    <input type="text" class="form-control" id="pymoUser" name="pymo_user"
                        value="{{ $store->pymo_user }}">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="pymoPassword">Contraseña PyMo</label>
                    <input type="password" class="form-control" id="pymoPassword"
                        name="pymo_password" value="{{ $store->pymo_password }}">
                </div>

                @if ($branchOffices)
                <div class="mb-3">
                    <label class="form-label" for="pymoBranchOfficeSelect">Seleccionar
                        Sucursal</label>
                    <select id="pymoBranchOfficeSelect" name="pymo_branch_office"
                        class="form-select">
                        <option value="">Selecciona una sucursal</option>
                        @foreach ($branchOffices as $branchOffice)
                        <option value="{{ $branchOffice['number'] }}"
                            data-callback-url="{{ $branchOffice['callbackNotificationUrl'] }}"
                            {{ $store->pymo_branch_office == $branchOffice['number'] ?
                            'selected' : '' }}>
                            {{ $branchOffice['fiscalAddress'] }}, {{ $branchOffice['city']
                            }}, {{ $branchOffice['state'] }} | Sucursal: {{
                            $branchOffice['number'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 d-none">
                    <label class="form-label" for="callbackNotificationUrl">URL de
                        notificaciones de la Sucursal</label>
                    <input type="text" class="form-control" id="callbackNotificationUrl"
                        name="callbackNotificationUrl" value="">
                </div>
                @endif
            </div>

            @if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password &&
            !empty($companyInfo))
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Logo de la empresa en Pymo</h4>
                    </div>
                    <div class="card-body">
                        @if ($logoUrl)
                        <div class="mb-3">
                            <img src="{{ asset($logoUrl) }}" alt="Company Logo"
                                class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        @endif
                        <form action="{{ route('accounting.uploadLogo') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <input type="file" class="form-control-file" id="logo"
                                    name="logo">
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Actualizar
                                Logo</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Información de la empresa en Pymo
                        </h4>
                    </div>
                    <div class="card-body">
                        <form>
                            @if (!empty($companyInfo['name']))
                            <div class="form-group">
                                <label for="companyName">Nombre de la
                                    Empresa</label>
                                <input type="text" class="form-control my-3"
                                    id="companyName" value="{{ $companyInfo['name'] }}"
                                    disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['rut']))
                            <div class="form-group">
                                <label for="companyRUT">RUT</label>
                                <input type="text" class="form-control my-3" id="companyRUT"
                                    value="{{ $companyInfo['rut'] }}" disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['socialPurpose']))
                            <div class="form-group">
                                <label for="socialPurpose">Propósito
                                    Social</label>
                                <input type="text" class="form-control my-3"
                                    id="socialPurpose"
                                    value="{{ $companyInfo['socialPurpose'] }}" disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['resolutionNumber']))
                            <div class="form-group">
                                <label for="resolutionNumber">Número de
                                    Resolución</label>
                                <input type="text" class="form-control my-3"
                                    id="resolutionNumber"
                                    value="{{ $companyInfo['resolutionNumber'] }}" disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['email']))
                            <div class="form-group">
                                <label for="companyEmail">Correo
                                    Electrónico</label>
                                <input type="email" class="form-control my-3"
                                    id="companyEmail" value="{{ $companyInfo['email'] }}"
                                    disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['createdAt']))
                            <div class="form-group">
                                <label for="createdAt">Fecha de
                                    Creación</label>
                                <input type="text" class="form-control my-3" id="createdAt"
                                    value="{{ $companyInfo['createdAt'] }}" disabled>
                            </div>
                            @endif

                            @if (!empty($companyInfo['updatedAt']))
                            <div class="form-group">
                                <label for="updatedAt">Fecha de
                                    Actualización</label>
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
</div>