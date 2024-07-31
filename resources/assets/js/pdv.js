$(document).ready(function() {
    const cashRegisterId = window.cashRegisterId;
    const baseUrl = window.baseUrl;
    const url = `${baseUrl}products/${cashRegisterId}`;
    let products = [];
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let isListView = false;
    let categories = [];
    let flavors = [];
    let productCategory = [];

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
                    actualizarCategoriasEnVista()
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Función para actualizar el menú desplegable de categorías en la vista
    function actualizarCategoriasEnVista() {
        let categoryHtml = '';
        productCategory.forEach(category => {
            categoryHtml += `
                <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="${category.id}" id="category-${category.category_id}">
                    <label class="form-check-label" for="category-${category.category_id}">${category.name}</label>
                </div>
            `;
        });
        $('#category-container').html(categoryHtml);
    }

    // Escuchar cambios en los checkboxes de las categorías
    $(document).on('change', '.form-check-input', function() {
        //filterProductsByCategory();
    });

    // Función para filtrar productos por categorías seleccionadas
    function filterProductsByCategory() {
        const selectedCategories = [];
        $('.form-check-input:checked').each(function() {
            selectedCategories.push(($(this).val()));
        });
        
        let filteredProducts=[];

        products.forEach(function(product) {
            productCategory.forEach(function(category) {
                if (category.product_id == product.id) {
                    selectedCategories.forEach(function(selectedCategory) {
                        if (category.category_id == parseInt(selectedCategory)) {
                            filteredProducts.push(product);
                        }
                    });
                }
            });
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
            productsHtml += `
                <div class="col-md-3 mb-2 card-product-pos d-flex align-items-stretch" data-category="${product.category}">
                    <div class="card-product-pos w-100 mb-3 position-relative">
                        <img src="${baseUrl}${product.image}" class="card-img-top-product-pos" alt="${product.name}">
                        <div class="card-img-overlay-product-pos d-flex flex-column justify-content-end">
                            <h5 class="card-title-product-pos">${product.name}</h5>
                            <p class="card-text-product-pos">$${product.price}</p>
                            <p class="card-text-product-pos"><del>$${product.old_price}</del></p>
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
            productsHtml += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="${baseUrl}${product.image}" class="img-thumbnail me-2" alt="${product.name}" style="width: 50px;">
                        <div>
                            <h5 class="mb-0">${product.name}</h5>
                            <small class="text-muted"><del>$${product.old_price}</del></small>
                            <p class="mb-0">$${product.price}</p>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm add-to-cart" data-id="${product.id}" data-type="${product.type}">Agregar</button>
                </li>
            `;
        });
        productsHtml += '</ul>';
        $('#products-container').html(productsHtml);
    }

    // Función para agregar producto al carrito
    function addToCart(productId, productType) {
        const product = products.find(p => p.id === productId);

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
                    price: product.price,
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
                    price: product.price,
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
            const itemTotal = item.price * item.quantity;
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
                                <li class="page-item">
                                    <span class="page-link">${item.quantity}</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link increase-quantity" href="javascript:void(0);" data-id="${item.id}"><i class="tf-icon bx bx-plus"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-from-cart" data-id="${item.id}"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            `;
        });

        const total = subtotal;

        $('#cart tbody').html(cartHtml);
        $('#cart tfoot .subtotal').text(`$${subtotal.toFixed(2)}`);
        $('#cart tfoot .total').text(`$${total.toFixed(2)}`);
        localStorage.setItem('cart', JSON.stringify(cart));
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
                const clients = response.clients;
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
            const clientItem = `<li class="list-group-item">${client.name} (${client.ci})</li>`;
            clientList.append(clientItem);
        });
    }

    // Filtrar clientes por búsqueda
    $('#search-client').on('input', function() {
        const query = $(this).val().toLowerCase();
        $('#client-list li').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
        });
    });

    // Cargar clientes al abrir el offcanvas
    $('#offcanvasEnd').on('show.bs.offcanvas', function() {
        loadClients();
    });

    //Cargar cliente

    document.getElementById('guardarCliente').addEventListener('click', function () {
        let nombre = document.getElementById('nombreCliente').value;
        let apellido = document.getElementById('apellidoCliente').value;
        let tipo = document.getElementById('tipoCliente').value;
        let email = document.getElementById('emailCliente').value;

        fetch('client', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: nombre,
                lastname: apellido,
                type: tipo,
                email: email
            })
        })
        .then(response => response.json())
        .then(data => {
            let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
            offcanvas.hide();
        })
        .catch((error) => {
            console.error('Error:', error);
        });
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
            return product.name.toLowerCase().includes(query.toLowerCase()) ||
                   product.sku.toLowerCase().includes(query.toLowerCase());
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

    //Cerrar caja

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
    updateCart();
});
