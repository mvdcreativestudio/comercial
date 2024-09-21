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

  var dt_product_stock_table = $('#product-list-container'); // Modificamos el selector para que apunte al div

  if (dt_product_stock_table.length) {
    var ajaxUrl = dt_product_stock_table.data('ajax-url');

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      success: function (response) {
        var rows = response.data;  // asumiendo que tu respuesta tiene la estructura {data: [...]}
        var cardContainer = $('#product-list-container').html(''); // Limpiamos el contenedor

        rows.forEach(function (rowData) {
          const stockClass =
            rowData.stock === 0 ? 'bg-danger' :
            rowData.stock <= rowData.safety_margin ? 'bg-warning' : 'bg-success';

          const statusBadge = rowData.status === 1 ? 'Activo' : 'Inactivo';
          const statusBadgeClass = rowData.status === 1 ? 'bg-success' : 'bg-danger';

          const card = `
            <div class="col-md-4 mb-4">
              <div class="card">
                <div class="row g-0">
                  <div class="col-md-4">
                    <img src="${baseUrl + rowData.image}" class="img-fluid rounded-start" alt="Imagen del producto">
                  </div>
                  <div class="col-md-8">
                    <div class="card-body">
                      <h5 class="card-title">${rowData.name}</h5>
                      <p class="card-text">Stock: <span class="badge ${stockClass}">${rowData.stock}</span></p>
                      <p class="card-text">Tienda: ${rowData.store_name}</p>
                      <p class="card-text">Estado: <span class="badge ${statusBadgeClass}">${statusBadge}</span></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;

          cardContainer.append(card); // Añadimos la tarjeta al contenedor
        });
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos:', error);
      }
    });
  }
});
