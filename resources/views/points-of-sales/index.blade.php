@extends('layouts.layoutMaster')

@section('title', 'Listado de Cajas Registradoras')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/bootstrap/bootstrap.min.css', // Bootstrap CSS
'resources/assets/vendor/libs/fontawesome/fontawesome.min.css' // FontAwesome CSS
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/bootstrap/bootstrap.bundle.min.js', // Bootstrap JS
'resources/assets/vendor/libs/fontawesome/fontawesome.min.js' // FontAwesome JS
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Gestión /</span> Listado de Cajas registradoras
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

<!-- Contenedor para el botón y la tabla -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Cajas registradoras</h5>
        <button id="crear-caja-btn" class="btn btn-primary">+ Crear</button>
        <a href="{{ route('pos-orders.index') }}" class="btn btn-secondary">Ver Órdenes POS</a>
    </div>

    <!-- Tabla de cajas registradoras -->
    <div class="card-datatable table-responsive p-3">
        <table class="table datatables-cash-registers border-top">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tienda</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cajas as $caja)
                <tr>
                    <td>{{ $caja->id }}</td>
                    <td>{{ $caja->store_id }}</td>
                    <td>{{ $caja->user_id }}</td>
                    <td>
                        <!-- Menú desplegable de tres puntos -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" type="button" id="dropdownMenuButton{{ $caja->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton{{ $caja->id }}">
                                <li>
                                    <button class="dropdown-item btn-open" data-id="{{ $caja->id }}">Abrir caja</button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-closed" data-id="{{ $caja->id }}">Cerrar Caja</button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-view" data-id="{{ $caja->id }}" data-store="{{ $caja->store_id }}" data-user="{{ $caja->user_id }}">Ver Detalles</button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-edit" data-id="{{ $caja->id }}" data-store="{{ $caja->store_id }}" data-user="{{ $caja->user_id }}">Editar</button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-delete" data-id="{{ $caja->id }}">Eliminar</button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para ingresar los datos de la caja registradora -->
<div class="modal fade" id="crearCajaModal" tabindex="-1" aria-labelledby="crearCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearCajaLabel">Crear Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="store_id" class="form-label">ID de Tienda:</label>
                    <input type="text" id="store_id" name="store_id" class="form-control">
                </div>
                <input type="hidden" id="user_id" name="user_id" value="{{ $userId }}">
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-crear-caja" class="btn btn-primary">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de la caja registradora -->
<div class="modal fade" id="detallesCajaModal" tabindex="-1" aria-labelledby="detallesCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detallesCajaLabel">Detalles de la Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="detalle_store_id" class="form-label">ID de Tienda:</label>
                    <input type="text" id="detalle_store_id" class="form-control" disabled>
                </div>
                <div class="mb-3">
                    <label for="detalle_user_id" class="form-label">ID de Usuario:</label>
                    <input type="text" id="detalle_user_id" class="form-control" disabled>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar la caja registradora -->
<div class="modal fade" id="editarCajaModal" tabindex="-1" aria-labelledby="editarCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCajaLabel">Editar Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_store_id" class="form-label">ID de Tienda:</label>
                    <input type="text" id="edit_store_id" name="edit_store_id" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="edit_user_id" class="form-label">ID de Usuario:</label>
                    <input type="text" id="edit_user_id" name="edit_user_id" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-editar-caja" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ingresar el monto inicial de la caja registradora -->
<div class="modal fade" id="abrirCajaModal" tabindex="-1" aria-labelledby="abrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="abrirCajaLabel">Abrir Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="initial_amount" class="form-label">Monto Inicial:</label>
                    <input type="number" id="initial_amount" name="initial_amount" class="form-control" required>
                </div>
                <input type="hidden" id="cash_register_id" name="cash_register_id">
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-abrir-caja" class="btn btn-primary">Abrir Caja</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cerrar la caja registradora -->
<div class="modal fade" id="cerrarCajaModal" tabindex="-1" aria-labelledby="cerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cerrarCajaLabel">Cerrar Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cash_register_id_close" name="cash_register_id_close">
                <p>¿Estás seguro de que deseas cerrar esta caja registradora?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="submit-cerrar-caja" class="btn btn-primary">Cerrar Caja</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
<script>
    $(document).ready(function() {

    $('.datatables-cash-registers').DataTable({
        "order": [[ 0, "desc" ]] 
    });
    
    var authenticatedUserId = @json($userId);

    // Mostrar el modal de crear al hacer clic en el botón de crear caja
    $('#crear-caja-btn').click(function() {
        $('#crearCajaModal').modal('show');
    });

    // Enviar los datos de la nueva caja registradora al servidor
    $('#submit-crear-caja').click(function() {
        var storeId = $('#store_id').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'points-of-sales',
            type: 'POST',
            data: {
                store_id: storeId,
                user_id: authenticatedUserId,
                _token: csrfToken
            },
            success: function(response) {
                $('#crearCajaModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function(xhr, status, error) {
                alert('Error al crear la caja registradora: ' + xhr.responseText);
            }
        });
    });

    // Mostrar el modal para abrir la caja con el monto inicial
    $('.btn-open').click(function() {
        var cashRegisterId = $(this).data('id');
        $('#cash_register_id').val(cashRegisterId);
        $('#abrirCajaModal').modal('show');
    });

    // Enviar los datos para abrir la caja registradora
    $('#submit-abrir-caja').click(function() {
        var cashRegisterId = $('#cash_register_id').val();
        var initialAmount = $('#initial_amount').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'pdv/open',
            type: 'POST',
            data: {
                cash_register_id: cashRegisterId,
                cash_float: initialAmount,
                _token: csrfToken
            },
            success: function(response) {
                $('#abrirCajaModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function(xhr, status, error) {
                alert('Error al abrir la caja registradora: ' + xhr.responseText);
            }
        });
    });

    // Manejar la eliminación de la caja registradora
    $('.btn-delete').click(function() {
        var id = $(this).data('id');
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        if (confirm('¿Estás seguro de que deseas eliminar esta caja registradora?')) {
            $.ajax({
                url: 'points-of-sales/' + id,
                type: 'DELETE',
                data: {
                    _token: csrfToken
                },
                success: function(response) {
                    location.reload(); // Recargar la página para reflejar los cambios
                },
                error: function(xhr, status, error) {
                    alert('Error al eliminar la caja registradora: ' + xhr.responseText);
                }
            });
        }
    });

    // Mostrar el modal de detalles con la información de la caja
    $('.btn-view').click(function() {
        var storeId = $(this).data('store');
        var userId = $(this).data('user');

        $('#detalle_store_id').val(storeId);
        $('#detalle_user_id').val(userId);
        $('#detallesCajaModal').modal('show');
    });

    // Mostrar el modal de edición con la información de la caja
    $('.btn-edit').click(function() {
        var id = $(this).data('id');
        var storeId = $(this).data('store');
        var userId = $(this).data('user');

        $('#edit_store_id').val(storeId);
        $('#edit_user_id').val(userId);
        $('#editarCajaModal').modal('show');

        // Manejar la actualización de la caja registradora
        $('#submit-editar-caja').click(function() {
            var updatedStoreId = $('#edit_store_id').val();
            var updatedUserId = $('#edit_user_id').val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: 'points-of-sales/' + id,
                type: 'PUT',
                data: {
                    store_id: updatedStoreId,
                    user_id: updatedUserId,
                    _token: csrfToken
                },
                success: function(response) {
                    $('#editarCajaModal').modal('hide');
                    location.reload(); // Recargar la página para reflejar los cambios
                },
                error: function(xhr, status, error) {
                    alert('Error al actualizar la caja registradora: ' + xhr.responseText);
                }
            });
        });
    });

    // Mostrar el modal de cierre con la información de la caja
    $('.btn-closed').click(function() {
        var cashRegisterId = $(this).data('id');
        $('#cash_register_id_close').val(cashRegisterId);
        $('#cerrarCajaModal').modal('show');
    });

    // Enviar los datos para cerrar la caja registradora
    $('#submit-cerrar-caja').click(function() {
        var cashRegisterId = $('#cash_register_id_close').val();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'pdv/close/' + cashRegisterId,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                $('#cerrarCajaModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function(xhr, status, error) {
                alert('Error al cerrar la caja registradora: ' + xhr.responseText);
            }
        });
    });
});  
</script>
@endsection