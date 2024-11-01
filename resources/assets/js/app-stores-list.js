document.addEventListener('DOMContentLoaded', function () {
  var dt_stores_table = $('.datatables-stores');

  var storeAdd = window.storeAdd;
  var storeEdit = window.storeEditTemplate;
  var storeManageUsers = window.storeManageUsersTemplate;
  var storeChangeStatus = window.toggleStoreStatus;

  if (dt_stores_table.length) {
    var table = dt_stores_table.DataTable({
      data: stores,
      columns: [
        { data: 'name' },
        {
          data: 'phone_number',
          render: function (data, type, row) {
            if (!data) {
              return 'Sin teléfono asociado';
            }
            return data.phone_number ? data.phone_number : 'Sin teléfono asociado';
          }
        },

        { data: 'email' },
        { data: 'address' },
        { data: 'rut' },
        { data: 'status' },
        {
          data: 'users_count',
          render: function (data, type, row) {
            return `<a href="${storeManageUsers.replace(':id', row.id)}">${data}</a>`;
          }
        }
      ],
      columnDefs: [
        {
          targets: 5,
          render: function (data, type, row, meta) {
            return data === 1
              ? '<span class="badge rounded-pill bg-success">Activa</span>'
              : '<span class="badge rounded-pill bg-danger">Inactiva</span>';
          }
        },
        {
          targets: 7,
          render: function (data, type, row, meta) {
            let actionButton =
              row.status === 1
                ? "<div class='dropdown-item text-danger delete-button' style='cursor: pointer;'><i class='bx bx-loader-circle'></i> Desactivar Empresa</div>"
                : "<div class='dropdown-item text-success delete-button' style='cursor: pointer;'><i class='bx bx-loader-circle'></i> Activar Empresa</div>";

            let automaticBillingButton = row.automatic_billing
              ? "<div class='dropdown-item text-danger delete-button' style='cursor: pointer;'><i class='bx bx-file'></i> Desactivar F. Automática</div>"
              : "<div class='dropdown-item text-success delete-button' style='cursor: pointer;'><i class='bx bx-file'></i> Activar F. Automática</div>";
            return `
            <div class="dropdown">
                <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-dots-horizontal-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="${storeEdit.replace(':id', row.id)}">
                    <i class="bx bx-pencil"></i> Editar
                  </a>
                  <a class="dropdown-item" href="${storeManageUsers.replace(':id', row.id)}">
                    <i class="bx bx-group"></i> Usuarios
                  </a>
                  <a class="dropdown-item" href="${storeManageHours.replace(':id', row.id)}">
                    <i class="bx bx-time"></i> Modificar Horarios
                  </a>
                  <form class="delete-form-${row.id}" action="${storeChangeStatus.replace(':id', row.id)}" method="POST">
                    <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                    ${actionButton}
                  </form>
                </div>
              </div>
            `;
          }
        }
      ],
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ empresas',
        paginate: {
          first: 'Primero',
          last: 'Último',
          next: '<span class="mx-2">Siguiente</span>',
          previous: '<span class="mx-2">Anterior</span>'
        },
        aria: {
          sortAscending: ': activar para ordenar la columna ascendente',
          sortDescending: ': activar para ordenar la columna descendente'
        },
        emptyTable: 'No hay datos disponibles en la tabla',
        zeroRecords: 'No se encontraron coincidencias',
        lengthMenu: '_MENU_',
        loadingRecords: 'Cargando...',
        processing: 'Procesando...',
        search: '',
        infoFiltered: '(filtrado de un total de _MAX_ empresas)',
        infoEmpty: 'Mostrando 0 a 0 de 0 empresas'
      },
      dom:
        '<"card-header d-flex border-top rounded-0 flex-wrap py-md-0"' +
        '<"me-5 ms-n2 pe-5"f>' +
        '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center mb-3 mb-sm-0"lB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      buttons: [
        {
          text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Crear</span>',
          className: 'btn btn-primary ml-3',
          action: function () {
            window.location.href = storeAdd;
          }
        }
      ]
    });
    $('.toggle-column').on('change', function () {
      var column = table.column($(this).attr('data-column'));
      column.visible(!column.visible());
    });
  }

  $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
  $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  $('.dt-buttons').addClass('d-flex flex-wrap');

  $('div.dataTables_filter input').addClass('form-control');
  $('div.dataTables_length select').addClass('form-select');

  $('.delete-button').click(function () {
    var form = $(this).closest('form');
    Swal.fire({
      title: '¿Seguro?',
      text: 'Estás a punto de cambiar el estado de la empresa',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, cambiarlo!',
      cancelButtonText: 'No, cancelar!',
      reverseButtons: true
    }).then(result => {
      if (result.isConfirmed) {
        form.submit();
      } else if (
        /* Read more about handling dismissals below */
        result.dismiss === Swal.DismissReason.cancel
      ) {
        Swal.fire('Cancelado', 'El estado de la empresa está seguro :)', 'error');
      }
    });
  });
});
