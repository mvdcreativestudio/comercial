'use strict';

$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_product_stock_table = $('#product-list-container');

  if (dt_product_stock_table.length) {
    var ajaxUrl = dt_product_stock_table.data('ajax-url');

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#product-list-container').html('');

        rows.forEach(function (rowData) {
          const stockClass =
            rowData.stock === 0 ? 'bg-danger' :
            rowData.stock <= rowData.safety_margin ? 'bg-warning' : 'bg-success';

          const statusBadge = rowData.status === 1 ? 'Activo' : 'Inactivo';
          const statusBadgeClass = rowData.status === 1 ? 'bg-success' : 'bg-danger';

          const card = `
            <div class="col-md-6 col-lg-4 col-12 mb-4">
              <div class="card h-100 shadow-sm">
                <div class="row g-0">
                  <div class="col-4 d-flex align-items-center">
                    <img src="${baseUrl + rowData.image}" class="img-fluid rounded-start w-100 h-auto object-fit-cover" alt="Imagen del producto" style="max-height: 150px;">
                  </div>
                  <div class="col-8">
                    <div class="card-body d-flex flex-column justify-content-between">
                      <div>
                        <h6 class="card-title">${rowData.name}</h6>
                        <p class="card-text mb-2">Stock: <span class="badge ${stockClass}">${rowData.stock}</span></p>
                        <p class="card-text mb-2">Tienda: ${rowData.store_name}</p>
                      </div>
                      <div>
                        <p class="card-text">Estado: <span class="badge ${statusBadgeClass}">${statusBadge}</span></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;

          cardContainer.append(card);
        });
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos:', error);
      }
    });
  }
});
