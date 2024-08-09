$(document).ready(function() {
  const cashRegisterId = window.cashRegisterId;
  const baseUrl = window.baseUrl;
  const url = `${baseUrl}products/${cashRegisterId}`;
  let products = [];
  let cart = [];
  let isListView = false;
  let categories = [];
  let flavors = [];
  let productCategory = [];
  let clients = [];

      // Inicializar Select2 en elementos con clase .select2
      $(function () {
        var select2 = $('.select2');
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    dropdownParent: $this.parent(),
                    placeholder: $this.data('placeholder')
                });
            });
        }
    });

  // Cargar el carrito desde el servidor
  function loadCart() {
      $.ajax({
          url: `cart`,
          type: 'GET',
          dataType: 'json',
          success: function(response) {
              if (response && response.cart) {
                  cart = response.cart;
                  updateCart();
              }
          },
          error: function(xhr, status, error) {
              console.error('Error al obtener el carrito:', error);
          }
      });
  }

  // Guardar el carrito en el servidor
  function saveCart() {
      $.ajax({
          url: `cart`,
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ cart: cart }),
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
      });
  }

  // Cargar las categorías y sus relaciones con los productos desde el backend
  function cargarCategorias() {
      $.ajax({
          url: `categories`,
          type: 'GET',
          success: function(response) {
              if (response && response.categories) {
                  categories = response.categories;
                  cargarCategoriaProducto();
              } else {
                  alert('No se encontraron categorías.');
              }
          },
          error: function(xhr, status, error) {
              alert('Error al cargar las categorías: ' + xhr.responseText);
          }
      });
  }

  // Cargar las categorías de los productos desde el backend
  function cargarCategoriaProducto() {
      $.ajax({
          url: `product-categories`,
          type: 'GET',
          success: function(response) {
              if (response) {
                  productCategory = response;
                  actualizarCategoriasEnVista();
              } else {
                  alert('No se encontraron categorías.');
              }
          },
          error: function(xhr, status, error) {
              alert('Error al cargar las categorías: ' + xhr.responseText);
          }
      });
  }

  // Evento de entrada para el campo de búsqueda de categorías
  $('#category-search-input').on('input', function() {
      const query = $(this).val();
      searchCategories(query);
  });

  // Función para actualizar el menú desplegable de categorías en la vista
  function actualizarCategoriasEnVista(categoriesToDisplay = productCategory) {
      let categoryHtml = '';
      categoriesToDisplay.forEach(category => {
          categoryHtml += `
              <div class="form-check form-check-primary mt-1">
                  <input class="form-check-input" type="checkbox" value="${category.id}" id="category-${category.category_id}" checked>
                  <label class="form-check-label" for="category-${category.category_id}">${category.name}</label>
              </div>
          `;
      });
      $('#category-container').html(categoryHtml);
  }

  // Escuchar cambios en los checkboxes de las categorías
  $(document).on('change', '.form-check-input', function() {
      filterProductsByCategory();
  });

  // Función para filtrar productos por categorías seleccionadas
  function filterProductsByCategory() {
      const selectedCategories = [];
      $('.form-check-input:checked').each(function() {
          selectedCategories.push(parseInt($(this).val()));
      });


      let filteredProducts = [];

      products.forEach(function(product) {
          const productCategories = categories.filter(category => category.product_id === product.id);

          const hasCategory = productCategories.some(category =>
              selectedCategories.includes(category.category_id)
          );

          if (hasCategory) {
              filteredProducts.push(product);
          }
      });

      if (isListView) {
          displayProductsList(filteredProducts);
      } else {
          displayProducts(filteredProducts);
      }
  }

  // Función para cargar productos
  function loadProducts() {
      $.ajax({
          url: `products/${cashRegisterId}`,
          type: 'GET',
          dataType: 'json',
          success: function(response) {
              if (response && response.products) {
                  products = response.products;
                  displayProducts(products);
              } else {
                  alert('No se encontraron productos.');
              }
          },
          error: function(xhr, status, error) {
              console.error('Error al obtener los productos:', error);
          }
      });
  }

  // Cargar sabores desde el backend
function cargarSabores() {
  $.ajax({
      url: `flavors`,
      type: 'GET',
      success: function(response) {
          if (response && response.flavors) {
              flavors = response.flavors;
              // Llenar el select con los sabores
              $('#flavorsSelect').empty();
              flavors.forEach(flavor => {
                  $('#flavorsSelect').append(new Option(flavor.name, flavor.id));
              });

              // Inicializar Select2 con formato de tags
              $('#flavorsSelect').select2({
                  tags: true,
                  placeholder: 'Selecciona sabores',
                  dropdownParent: $('#flavorModal')
              });
          } else {
              alert('No se encontraron sabores.');
          }
      },
      error: function(xhr, status, error) {
          alert('Error al cargar los sabores: ' + xhr.responseText);
      }
  });
}


// Función para mostrar productos en formato de tarjetas
function displayProducts(productsToDisplay) {
  let productsHtml = '';
  productsToDisplay.forEach(product => {
      const priceToDisplay = product.price ? product.price : product.old_price;

      productsHtml += `
        <div class="col-6 col-md-3 mb-2 card-product-pos d-flex align-items-stretch" data-category="${product.category}">
            <div class="card-product-pos w-100 mb-3 position-relative">
                <img src="${baseUrl}${product.image}" class="card-img-top-product-pos" alt="${product.name}">
                <div class="card-img-overlay-product-pos d-flex flex-column justify-content-end">
                    <h5 class="card-title-product-pos text-white">${product.name}</h5>
                    <p class="card-text-product-pos">
                        ${product.price ? `<span class="text-muted" style="font-size: 0.8em;"><del>$${product.old_price}</del></span>` : ''}
                        <span style="font-size: 1em;">$${priceToDisplay}</span>
                    </p>
                    <button class="btn btn-primary btn-sm add-to-cart" data-id="${product.id}" data-type="${product.type}">Agregar</button>
                </div>
            </div>
        </div>
      `;
  });
  $('#products-container').html(productsHtml);
}


  // Función para mostrar productos en formato de lista
  function displayProductsList(productsToDisplay) {
      let productsHtml = '<ul class="list-group w-100">';
      productsToDisplay.forEach(product => {
          const priceToDisplay = product.price ? product.price : product.old_price; // Usar price si existe, de lo contrario old_price

          productsHtml += `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                      <img src="${baseUrl}${product.image}" class="img-thumbnail me-2" alt="${product.name}" style="width: 50px;">
                      <div>
                          <h5 class="mb-0">${product.name}</h5>
                          ${product.price ? `<small class="text-muted"><del>$${product.old_price}</del></small>` : ''}
                          <p class="mb-0">$${priceToDisplay}</p>
                      </div>
                  </div>
                  <button class="btn btn-primary btn-sm add-to-cart" data-id="${product.id}" data-type="${product.type}">Agregar</button>
              </li>
          `;
      });
      productsHtml += '</ul>';
      $('#products-container').html(productsHtml);
  }

  // Función para agregar a un producto al carrito
  function addToCart(productId, productType) {
    const product = products.find(p => p.id === productId);

    // Determinar el precio a usar
    const priceToUse = product.price ? product.price : product.old_price;

    if (productType === 'configurable') {
        // Mostrar el modal para seleccionar sabores
        $('#flavorModal').modal('show');

        // Guardar el producto temporalmente hasta que se seleccionen los sabores
        $('#saveFlavors').off('click').on('click', function() {
            const selectedFlavors = $('#flavorsSelect').val();
            if (selectedFlavors.length === 0) {
                alert('Debe seleccionar al menos un sabor.');
                return;
            }

            var category = categories.find(category => category.product_id == product.id);
            var category_id = category ? category.category_id : null;
            // Agregar el producto como nuevo ítem en el carrito
            cart.push({
                id: product.id,
                name: product.name,
                image: product.image,
                price: priceToUse,
                flavors: selectedFlavors,
                quantity: 1,
                category_id: category_id
            });

            updateCart();
            $('#flavorModal').modal('hide');
        });
    } else {
        const cartItem = cart.find(item => item.id === productId && item.flavors.length === 0);
        var category = categories.find(category => category.product_id == product.id);
        var category_id = category ? category.category_id : null;
        if (cartItem) {
            cartItem.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                image: product.image,
                price: priceToUse,
                flavors: [],
                quantity: 1,
                category_id: category_id
            });
        }

        updateCart();
    }
}

// Función para actualizar el carrito en el DOM
function updateCart() {
    let cartHtml = '';
    let subtotal = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;  // Usar el precio ya calculado en addToCart
        subtotal += itemTotal;

        cartHtml += `
            <tr>
                <td>
                    <img src="${baseUrl}${item.image}" alt="${item.name}" class="img-thumbnail me-2" style="width: 50px;">
                    ${item.name}
                </td>
                <td>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link decrease-quantity" href="javascript:void(0);" data-id="${item.id}"><i class="tf-icon bx bx-minus"></i></a>
                            </li>
                            <li class="page-item active"><a class="page-link">${item.quantity}</a></li>
                            <li class="page-item">
                                <a class="page-link increase-quantity" href="javascript:void(0);" data-id="${item.id}"><i class="tf-icon bx bx-plus"></i></a>
                            </li>
                        </ul>
                    </nav>
                </td>
                <td>
                    $${item.price}  <!-- Mostrar el precio utilizado -->
                </td>
                <td>
                    $${itemTotal.toFixed(2)}
                </td>
                <td>
                    <button class="btn btn-danger btn-sm remove-from-cart" data-id="${item.id}">X</button>
                </td>
            </tr>
        `;
    });

    const total = subtotal;

    $('#cart tbody').html(cartHtml);
    $('.subtotal').text(`$${subtotal.toFixed(2)}`);
    $('.total').text(`$${total.toFixed(2)}`);

    // Guardar el carrito en el servidor
    saveCart();
}

// Manejar el clic en el botón "Agregar al carrito"
$(document).on('click', '.add-to-cart', function() {
    const productId = $(this).data('id');
    const productType = $(this).data('type');
    addToCart(productId, productType);
});

// Manejar el clic en los botones para aumentar/disminuir cantidad
$(document).on('click', '.increase-quantity', function() {
    const productId = $(this).data('id');
    const cartItem = cart.find(item => item.id === productId);
    cartItem.quantity += 1;
    updateCart();
});

$(document).on('click', '.decrease-quantity', function() {
    const productId = $(this).data('id');
    const cartItem = cart.find(item => item.id === productId);
    if (cartItem.quantity > 1) {
        cartItem.quantity -= 1;
    } else {
        cart = cart.filter(item => item.id !== productId);
    }
    updateCart();
});

// Manejar el clic en el botón "Eliminar del carrito"
$(document).on('click', '.remove-from-cart', function() {
    const productId = $(this).data('id');
    cart = cart.filter(item => item.id !== productId);
    updateCart();
});

// Función para cargar clientes
function loadClients() {
    $.ajax({
        url: 'clients/json',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            clients = response.clients;
            const clientCount = response.count;
            if (clientCount > 0) {
                $('#search-client-container').show();
            } else {
                $('#search-client-container').hide();
            }
            displayClients(clients);
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener los clientes:', error);
        }
    });
}

// Función para mostrar los clientes
function displayClients(clients) {
    const clientList = $('#client-list');
    clientList.empty();
    clients.forEach(client => {
        const fullName = `${client.name} ${client.lastname}`;
        const clientItem = `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>${fullName}</strong><br>
                    <small>CI: ${client.ci}</small><br>
                    <small>RUT: ${client.rut}</small>
                </div>
                <button class="btn btn-primary btn-sm add-client" data-client='${JSON.stringify(client)}'>
                    <i class="bx bx-plus"></i>
                </button>
            </li>
        `;
        clientList.append(clientItem);
    });

    // Manejar el clic en el botón "+"
    $('.add-client').on('click', function() {
        const client = $(this).data('client');
        saveClientToSession(client);

        // Actualizar el botón "Seleccionar cliente" con el nombre completo del cliente
        const buttonSelector = document.querySelector('[data-bs-target="#offcanvasEnd"]');
        if (buttonSelector) {
            const fullName = `${client.name} ${client.lastname}`;
            buttonSelector.textContent = fullName;
        }

        // Cerrar el modal
        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEnd'));
        offcanvas.hide();
    });
}

function saveClientToSession(client) {
    $.ajax({
        url: 'client-session',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            client: client
        },
    });
}

// Filtrar clientes por búsqueda
$('#search-client').on('input', function() {
    const query = $(this).val().toLowerCase();
    searchClients(query);
});

// Cargar clientes al abrir el offcanvas
$('#offcanvasEnd').on('show.bs.offcanvas', function() {
    loadClients();
});

function searchClients(query) {
    const filteredClients = clients.filter(client => {
        const clientName = client.name ? client.name.toLowerCase() : '';
        const clientCI = client.ci ? client.ci.toLowerCase() : '';
        const clientRUT = client.rut ? client.rut.toLowerCase() : '';
        return clientName.includes(query.toLowerCase()) ||
               clientCI.includes(query.toLowerCase()) ||
               clientRUT.includes(query.toLowerCase());
    });

    displayClients(filteredClients); // Muestra los clientes filtrados
}

// Guardar cliente en base de datos
document.getElementById('guardarCliente').addEventListener('click', function () {
    let nombre = document.getElementById('nombreCliente').value;
    let apellido = document.getElementById('apellidoCliente').value;
    let tipo = document.getElementById('tipoCliente').value;
    let email = document.getElementById('emailCliente').value;
    let ci = document.getElementById('ciCliente').value;
    let rut = document.getElementById('rutCliente').value;

    let data = {
        name: nombre,
        lastname: apellido,
        type: tipo,
        email: email
    };

    if (tipo === 'individual') {
        data.ci = ci;
    } else if (tipo === 'company') {
        data.rut = rut;
    }

    fetch('client', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
        offcanvas.hide();
        document.getElementById('formCrearCliente').reset();
    })
    .catch((error) => {
        console.error('Error:', error);
    });
});

document.getElementById('tipoCliente').addEventListener('change', function() {
    let tipo = this.value;
    if (tipo === 'individual') {
        document.getElementById('ciField').style.display = 'block';
        document.getElementById('rutField').style.display = 'none';
    } else if (tipo === 'company') {
        document.getElementById('ciField').style.display = 'none';
        document.getElementById('rutField').style.display = 'block';
    }
});

// Manejar el cambio de vista de productos (tarjeta/lista)
$('#toggle-view-btn').on('click', function() {
    isListView = !isListView;
    $(this).find('i').toggleClass('bx-list-ul bx-grid-alt');
    if (isListView) {
        displayProductsList(products);
    } else {
        displayProducts(products);
    }
});

// Filtrar productos por búsqueda
function searchProducts(query) {
    const filteredProducts = products.filter(product => {
        const productName = product.name ? product.name.toLowerCase() : '';
        const productSku = product.sku ? product.sku.toLowerCase() : '';
        return productName.includes(query.toLowerCase()) || productSku.includes(query.toLowerCase());
    });
    if (isListView) {
        displayProductsList(filteredProducts);
    } else {
        displayProducts(filteredProducts);
    }
}

// Manejar cambios en la barra de búsqueda
$('#html5-search-input').on('input', function() {
    const query = $(this).val();
    searchProducts(query);
});

// Mostrar el modal de cerrar caja al hacer clic en el botón correspondiente
$('#btn-cerrar-caja').click(function() {
    var cashRegisterId = $(this).data('id');
    $('#cash_register_id_close').val(cashRegisterId);
    $('#cerrarCajaModal').modal('show');
});

// Enviar la solicitud para cerrar la caja registradora
$('#submit-cerrar-caja').click(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: 'close/' + cashRegisterId,
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

// Inicializar funciones
loadProducts();
cargarCategorias();
cargarSabores();
cargarCategoriaProducto();
loadCart();
});
