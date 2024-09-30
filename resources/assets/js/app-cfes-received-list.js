$(function () {
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-cfes-received').data('symbol');

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_cfe_table = $('.datatables-cfes-received');

  $.fn.dataTable.ext.errMode = 'throw';

  if (dt_cfe_table.length) {
    try {
      var dt_cfes = dt_cfe_table.DataTable({
        ajax: {
          url: 'admin/cfes/received/datatable',
          dataSrc: 'data'
        },
        columns: [
          { data: 'id', type: 'num' },
          { data: 'issuer_name' },
          { data: 'emition_date' },
          { data: 'total' },
          { data: 'currency' },
          { data: 'total' },
          { data: 'reason' },
          { data: 'actions' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            render: function (data, type, full, meta) {
              return '#' + data;
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return full['issuer_name'];
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              return data ? moment(data).format('DD-MM-YYYY HH:mm') : 'Fecha inválida';
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              return $currencySymbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              return full['currency'];
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              return $currencySymbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return data ? data : 'Sin Razón';
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
                '<a href="#" class="dropdown-item btn-ver-detalles" data-id="' +
                full['id'] +
                '">Ver Detalles</a>' +
                '</div>' +
                '</div>'
              );
            }
          }
        ],
        order: [[0, 'desc']],
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
          info: 'Mostrando _START_ a _END_ de _TOTAL_ CFEs recibidos',
          infoFiltered: 'filtrados de _MAX_ CFEs recibidos',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay CFEs recibidos disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        },
        rowCallback: function (row, data, index) {
          if (data['total'] > 1000) {
            $('td', row).eq(3).css('background-color', '#F5F5F9').css('color', '#566A7F');
          }
        }
      });

      $('.datatables-cfes-received tbody').on('click', '.btn-ver-detalles', function () {
        var cfe = dt_cfes.row($(this).parents('tr')).data();

        $('#modalDetalle .modal-title').text('Detalles del CFE Recibido');
        $('#modalDetalle .modal-body').html(`
          <p><strong>Serie:</strong> ${cfe.serie}</p>
          <p><strong>CFE ID:</strong> ${cfe.cfeId}</p>
          <p><strong>Número:</strong> ${cfe.nro}</p>
          <p><strong>CAE Number:</strong> ${cfe.caeNumber}</p>
          <p><strong>CAE Range:</strong> ${cfe.caeRange}</p>
          <p><strong>CAE Expiration Date:</strong> ${moment(cfe.caeExpirationDate).format('DD-MM-YYYY')}</p>
          <p><strong>Total:</strong> ${$currencySymbol}${cfe.total}</p>
          <p><strong>Emisión Date:</strong> ${moment(cfe.emitionDate).format('DD-MM-YYYY')}</p>
          <p><strong>Hash:</strong> ${cfe.sentXmlHash}</p>
          <p><strong>Security Code:</strong> ${cfe.securityCode}</p>
          <p><strong>QR URL:</strong> <a href="${cfe.qrUrl}" target="_blank">${cfe.qrUrl}</a></p>
        `);

        $('#modalDetalle').modal('show');
      });

      $('.toggle-column').on('change', function () {
        var column = dt_cfes.column($(this).attr('data-column'));
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
