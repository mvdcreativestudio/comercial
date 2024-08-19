$(function () {
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-receipt').data('symbol');

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_receipt_table = $('.datatables-receipt');

  $.fn.dataTable.ext.errMode = 'throw';

  if (dt_receipt_table.length) {
    try {
      var dt_receipts = dt_receipt_table.DataTable({
        ajax: {
          url: 'receipts/datatable',
          dataSrc: 'data'
        },
        columns: [
          { data: 'id' },
          { data: 'store_name' },
          { data: 'client_name' },
          { data: 'date' },
          { data: 'type' },
          { data: 'currency' },
          { data: 'total' },
          { data: 'actions' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            render: function (data, type, full, meta) {
              return (
                '<a href="' +
                baseUrl +
                'admin/receipts/' +
                full['order_uuid'] +
                '/show" class="text-body">#' +
                data +
                '</a>'
              );
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return full['store_name'];
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              var $name = full['client_name'] + ' ' + full['client_lastname'],
                $initials = $name.replace(/[^A-Z]/g, '').substring(0, 2),
                stateNum = Math.floor(Math.random() * 6),
                states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'],
                $state = states[stateNum],
                $avatar = full['client_avatar'];

              return (
                '<div class="d-flex justify-content-start align-items-center">' +
                '<div class="avatar me-2">' +
                ($avatar
                  ? '<img src="' + $avatar + '" alt="Avatar" class="rounded-circle">'
                  : '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>') +
                '</div>' +
                '<div class="d-flex flex-column">' +
                '<h6 class="mb-0">' +
                $name +
                '</h6>' +
                '</div>' +
                '</div>'
              );
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              return data ? moment(data.date).format('DD-MM-YYYY HH:mm') : 'Fecha inválida';
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              return data;
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              return full['currency'];
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return $currencySymbol + data;
            }
          },
          {
            targets: -1,
            orderable: false,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-flex justify-content-center align-items-center">' +
                '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
                '<div class="dropdown-menu dropdown-menu-end m-0">' +
                '<a href="' +
                full['qrUrl'] +
                '" target="_blank" class="dropdown-item">Ver QR</a>' +
                '<a href="' +
                baseUrl +
                'admin/orders/' +
                full['order_uuid'] +
                '" class="dropdown-item">Ver Orden</a>' +
                '<a href="#" class="dropdown-item btn-ver-detalles" data-id="' +
                full['id'] +
                '">Ver Detalles</a>' +
                '</div>' +
                '</div>'
              );
            }
          }
        ],
        order: [3, 'desc'],
        dom:
          '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
          '>t' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        lengthMenu: [10, 25, 50, 100],
        language: {
          search: '',
          searchPlaceholder: 'Buscar...',
          sLengthMenu: '_MENU_',
          info: 'Mostrando _START_ a _END_ de _TOTAL_ facturas',
          infoFiltered: 'filtrados de _MAX_ facturas',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay facturas disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        }
      });

      // Evento para mostrar el modal con detalles
      $('.datatables-receipt tbody').on('click', '.btn-ver-detalles', function () {
        var receipt = dt_receipts.row($(this).parents('tr')).data();

        $('#modalDetalle .modal-title').text('Detalles del CFE');
        $('#modalDetalle .modal-body').html(`
          <p><strong>Serie:</strong> ${receipt.serie}</p>
          <p><strong>CFE ID:</strong> ${receipt.cfeId}</p>
          <p><strong>Número:</strong> ${receipt.nro}</p>
          <p><strong>CAE Number:</strong> ${receipt.caeNumber}</p>
          <p><strong>CAE Range:</strong> ${receipt.caeRange}</p>
          <p><strong>CAE Expiration Date:</strong> ${moment(receipt.caeExpirationDate).format('DD-MM-YYYY')}</p>
          <p><strong>Total:</strong> ${$currencySymbol}${receipt.total}</p>
          <p><strong>Emisión Date:</strong> ${moment(receipt.emitionDate).format('DD-MM-YYYY')}</p>
          <p><strong>Hash:</strong> ${receipt.sentXmlHash}</p>
          <p><strong>Security Code:</strong> ${receipt.securityCode}</p>
          <p><strong>QR URL:</strong> <a href="${receipt.qrUrl}" target="_blank">${receipt.qrUrl}</a></p>
        `);

        $('#modalDetalle').modal('show');
      });

      $('.toggle-column').on('change', function () {
        var column = dt_receipts.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      // Estilos buscador y paginación
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');
    } catch (error) {
      console.log(error);
    }
  }
});
