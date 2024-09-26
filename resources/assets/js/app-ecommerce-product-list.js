'use strict';

$(function () {
  let borderColor, bodyBg, headingColor;
  let currencySymbol = window.currencySymbol;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_product_list_container = $('#product-list-container');
  var searchInput = $('#searchProduct');
  var storeFilter = $('#storeFilter');
  var categoryFilter = $('#categoryFilter');
  var statusFilter = $('#statusFilter');

  function isFilterApplied() {
    return (
      searchInput.val().trim() !== '' ||
      storeFilter.val() !== '' ||
      categoryFilter.val() !== '' ||
      statusFilter.val() !== ''
    );
  }

  function resetFilters() {
    searchInput.val('');
    storeFilter.val('');
    categoryFilter.val('');
    statusFilter.val('');
    fetchProducts();
  }

  function fetchProducts() {
    var ajaxUrl = dt_product_list_container.data('ajax-url');
    var searchQuery = searchInput.val();
    var storeId = storeFilter.val();
    var categoryId = categoryFilter.val();
    var status = statusFilter.val();

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      data: {
        search: searchQuery,
        store_id: storeId,
        category_id: categoryId,
        status: status
      },
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#product-list-container').html(''); // Limpiar el contenedor

        if (rows.length === 0) {
          if (isFilterApplied()) {
            cardContainer.html(`
              <div class="alert alert-warning text-center w-100">
                <i class="bx bx-filter-alt"></i> No hay productos que coincidan con los filtros.
                <br>
                <button id="clearFilters" class="btn btn-outline-danger mt-3">Borrar filtros</button>
              </div>
            `);
            $('#clearFilters').on('click', function () {
              resetFilters();
            });
          } else {
            cardContainer.html(`
              <div class="alert alert-info text-center w-100">
                <i class="bx bx-info-circle"></i> No existen productos disponibles.
              </div>
            `);
          }
        } else {
          rows.forEach(function (rowData) {
            const stockClass =
              rowData.stock === 0 ? 'bg-danger' :
              rowData.stock <= rowData.safety_margin ? 'bg-warning' : 'bg-success';

            const statusText = rowData.status === 1 ? 'Activo' : 'Inactivo';
            const statusTextClass = rowData.status === 1 ? 'text-success' : 'text-danger';
            const truncatedName = rowData.name.length > 25 ? rowData.name.substring(0, 25) + '...' : rowData.name;

            const card = `
              <div class="col-md-6 col-lg-4 col-12 mb-4">
                <div class="product-card">
                  <div class="col-4 d-flex align-items-center">
                    <img src="${baseUrl + rowData.image}" class="img-fluid product-card-img" alt="Imagen del producto">
                  </div>
                  <div class="col-8">
                    <div class="product-card-body">
                      <h5 class="product-title">${truncatedName}</h5>
                      <p class="product-category text-muted small">${rowData.category || 'Sin categoría'}</p>
                      <h6 class="product-price">${currencySymbol}${parseFloat(rowData.price).toFixed(2)}</h6>
                      <p class="product-stock"><span class="badge ${stockClass}">${rowData.stock}</span></p>
                      <p class="product-status ${statusTextClass}">${statusText}</p>
                      <div class="product-card-actions">
                        <a href="${baseUrl}admin/products/${rowData.id}/edit" class="btn btn-sm btn-icon">
                          <i class="bx bx-edit"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `;

            cardContainer.append(card);
          });
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos:', error);
      }
    });
  }

  // Abrir el modal de importación
  $('#openImportModal').on('click', function () {
    $('#importModal').modal('show');
  });

  // Fetch products on page load
  fetchProducts();

  // Trigger search on input change
  searchInput.on('input', function () {
    fetchProducts();
  });

  // Trigger fetch on filter change
  storeFilter.on('change', function () {
    fetchProducts();
  });

  categoryFilter.on('change', function () {
    fetchProducts();
  });

  statusFilter.on('change', function () {
    fetchProducts();
  });

  // Abrir y cerrar el modal de filtros
  $('#openFilters').on('click', function () {
    $('#filterModal').addClass('open');
  });

  $('#closeFilterModal').on('click', function () {
    $('#filterModal').removeClass('open');
  });

  // Capturar los filtros y enviarlos para exportar a Excel
  $('#exportExcel').on('click', function () {
    var searchQuery = searchInput.val();
    var storeId = storeFilter.val();
    var categoryId = categoryFilter.val();
    var status = statusFilter.val();

    var params = {
      search: searchQuery,
      store_id: storeId,
      category_id: categoryId,
      status: status
    };

    var queryString = $.param(params);

    console.log(exportUrl + '?' + queryString);

    window.location.href = exportUrl + '?' + queryString;
  });

});
