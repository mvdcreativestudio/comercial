$(document).ready(function () {
  let cart = [];
  const baseUrl = window.baseUrl || '';
  const frontRoute = window.frontRoute || '';
  let client = [];
  const cashRegisterId = window.cashRegisterId;
  let cashRegisterLogId = null;
  let sessionStoreId = null;
  let discount = 0;
  let coupon = null;
  let currencySymbol = window.currencySymbol;
  let posResponsesConfig = {};
  $('#client-info').hide();


  // INTEGRACIÓN POS

  // Cargar la configuración de respuestas POS desde el backend
  function loadPosResponses() {
    $.ajax({
        url: `${baseUrl}api/pos/responses`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            // Almacenar la configuración en la variable global
            posResponsesConfig = response;
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar la configuración de respuestas:', error);
        }
    });
  }

  // Llama a la función para cargar la configuración al inicio
  loadPosResponses();

  function obtenerTokenPos() {
    return $.ajax({
        url: `${baseUrl}api/pos/token`, // Cambiado a la ruta genérica para obtener token POS
        type: 'GET',
        success: function (response) {
            if (response.access_token) {
                return response.access_token;
            } else {
                console.error('Error: no se recibió un token válido');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener el token del POS:', error);
        }
    });
  }

  function enviarTransaccionPos(token) {
    const posID = $('#posID').val() || "7";
    const empresa = $('#empresa').val() || "2024";
    const local = $('#local').val() || "1";
    const caja = $('#caja').val() || "7";
    const userId = $('#userId').val() || "Usuario1";
    const now = new Date();
    const transactionDateTime = now.getFullYear().toString() +
        String(now.getMonth() + 1).padStart(2, '0') +
        String(now.getDate()).padStart(2, '0') +
        String(now.getHours()).padStart(2, '0') +
        String(now.getMinutes()).padStart(2, '0') +
        String(now.getSeconds()).padStart(2, '0');

    const amount = parseFloat($('.total').text().replace('$', ''));
    const quotas = 1.5;
    const plan = 1;
    const currency = "858";
    const taxableAmount = amount;
    const invoiceAmount = amount;
    const taxAmount = amount * 2;
    const ivaAmount = amount * 2;
    const needToReadCard = false;

    const transactionData = {
        PosID: posID,
        Empresa: empresa,
        Local: local,
        Caja: caja,
        UserId: userId,
        TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
        Amount: amount.toString() + "00",
        Quotas: quotas,
        Plan: plan,
        Currency: currency,
        TaxableAmount: taxableAmount.toString() + "00",
        InvoiceAmount: invoiceAmount.toString() + "00",
        TaxAmount: taxAmount.toString() + "00",
        IVAAmount: ivaAmount.toString() + "00",
        NeedToReadCard: needToReadCard
    };

    showTransactionStatus(10, false, true);

    $.ajax({
        url: `${baseUrl}api/pos/process-transaction`,
        type: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`  // Usando el token aquí
        },
        data: JSON.stringify(transactionData),
        success: function (response) {
            const transactionId = response.TransactionId;
            const sTransactionId = response.STransactionId;

            if (transactionId && sTransactionId) {
                // Guardar los IDs en la sesión del navegador
                sessionStorage.setItem('TransactionId', transactionId);
                sessionStorage.setItem('STransactionId', sTransactionId);

                consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token);
            } else {
                showTransactionStatus(999); // Error desconocido si no hay IDs válidos
            }
        },
        error: function (xhr, status, error) {
            showTransactionStatus(999); // Mostrar error desconocido
            console.error('Error al enviar la transacción a Scanntech:', error);
        }
    });
  }


  // Función para consultar el estado de la transacción
function consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token) {
  let attempts = 0;
  const maxAttempts = 30; // Número máximo de intentos
  let isTransactionComplete = false; // Variable de control para detener la consulta si la transacción ha sido completada

  function poll() {
      if (attempts >= maxAttempts) {
          showTransactionStatus('Tiempo de espera excedido al consultar el estado de la transacción.', true);
          return;
      }

      if (isTransactionComplete) {
          return;
      }

      setTimeout(function () {
          attempts++;

          const dataToSend = {
              PosID: $('#posID').val() || "7",
              Empresa: $('#empresa').val() || "2024",
              Local: $('#local').val() || "1",
              Caja: $('#caja').val() || "7",
              UserId: $('#userId').val() || "Usuario1",
              TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
              TransactionId: transactionId,
              STransactionId: sTransactionId
          };

          if (attempts === 1) {
              showTransactionStatus('Transacción en progreso...', false, true);
          }

          $.ajax({
              url: `${baseUrl}api/pos/check-transaction-status`,
              type: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Authorization': `Bearer ${token}`
              },
              data: JSON.stringify(dataToSend),
              success: function (response) {
                  const responseCode = response.responseCode;
                  showTransactionStatus(responseCode, false, false);

                  console.log('Código de respuesta recibido:', responseCode);


                  // Manejo de diferentes códigos de respuesta
                  if (responseCode === 10 || responseCode === 113 || responseCode === 12 || responseCode === 0) {
                      // Continuar consultando mientras se reciba uno de estos códigos
                      poll();
                  } else if (responseCode === 111) {
                      if (swalInstance) {
                          swalInstance.close(); // Cerrar swal si está abierto
                      }
                      isTransactionComplete = true; // Marcar la transacción como completa para detener consultas adicionales
                      console.log('Llamando a postOrder() después de recibir el código de transacción finalizada');
                      postOrder(); // Llamar a la función postOrder() para procesar la orden
                  } else {
                      console.error('Código de respuesta no esperado:', responseCode);
                      showTransactionStatus(`Error inesperado: Código de respuesta ${responseCode}`, true);
                  }
              },
              error: function (xhr) {
                  console.error('Error al consultar el estado de la transacción:', xhr);
                  showTransactionStatus(`Error al consultar el estado: ${xhr.status} - ${xhr.responseText}`, true);
              }
          });
      }, 2000); // Intervalo de 2 segundos entre cada consulta
    }

    poll(); // Iniciar el ciclo de consultas
  }


  let swalInstance;

  // Función para mostrar el mensaje de estado de la transacción
  function showTransactionStatus(code, isError = false, isInitial = false) {
    if (!posResponsesConfig || !posResponsesConfig[code]) {
        // Default message if no response config is found for the given code
        const defaultConfig = {
            message: 'Error desconocido.',
            icon: 'error',
            showCloseButton: true
        };
        showSweetAlert(defaultConfig, isInitial);
        return;
    }

    const responseConfig = posResponsesConfig[code];

    showSweetAlert(responseConfig, isInitial);
  }

  // Función para mostrar el mensaje de respuesta POS en SweetAlert
  function showSweetAlert(responseConfig, isInitial) {
    if (isInitial) {
        swalInstance = Swal.fire({
            icon: responseConfig.icon || 'question',
            title: 'Estado de Transacción',
            html: responseConfig.message,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    } else {
        if (swalInstance) {
            swalInstance.update({
                icon: responseConfig.icon,
                html: responseConfig.message,
                showConfirmButton: responseConfig.showCloseButton,
                confirmButtonText: 'Cerrar',
                allowOutsideClick: responseConfig.showCloseButton
            });

            if (!responseConfig.message.includes('en progreso') && !responseConfig.message.includes('Esperando por operación en el PINPad')) {
                Swal.hideLoading();
            }

            if (responseConfig.showCloseButton) {
                swalInstance.then(() => {
                    Swal.close();
                    window.history.back();
                });
            }
        }
    }
  }

  // Función para verificar si el usuario tiene permiso para ver las ordenes
  function userHasPermission(permission) {
    // Chequear si la lista de permisos contiene el permiso buscado
    return window.userPermissions && window.userPermissions.includes(permission);
  }

  function mostrarError(mensaje) {
    $('#errorContainer').text(mensaje).removeClass('d-none'); // Mostrar mensaje de error
  }

  function ocultarError() {
    $('#errorContainer').addClass('d-none'); // Ocultar el contenedor de errores
  }

  function obtenerCashRegisterLogId() {
    if (cashRegisterId) {
      $.ajax({
        url: `log/${cashRegisterId}`,
        type: 'GET',
        success: function (response) {
          cashRegisterLogId = response.cash_register_log_id;
          sessionStoreId = response.store_id; // Ahora obtenemos el store_id directamente
          console.log("ID de cash register log obtenido:", cashRegisterLogId);
          console.log("Store ID obtenido:", sessionStoreId);
        },
        error: function (xhr) {
          mostrarError('Error al obtener el ID de cash register log: ' + xhr.responseText);
        }
      });
    } else {
      console.error('ID de caja registradora no definido');
    }
  }



  function loadCartFromSession() {
    $.ajax({
      url: `cart`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (Array.isArray(response.cart)) {
          cart = response.cart;
        } else {
          cart = [];
        }
        updateCheckoutCart();
      },
      error: function (xhr) {
        mostrarError('Error al cargar el carrito desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function loadClientFromSession() {
    $.ajax({
      url: `client-session`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        client = response.client;

        if (client && client.id) {
          showClientInfo(client);
          $('#client-selection-container').hide();
        }
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function loadStoreIdFromSession() {
    $.ajax({
      url: `storeid-session`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        sessionStoreId = response.id;
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function showClientInfo(client) {
    // Verifica si el cliente es una persona o una empresa
    const clientType = client.type === 'company' ? 'Empresa' : 'Persona';
    const clientDocLabel = client.type === 'company' ? 'RUT' : 'CI';
    const clientDoc = client.type === 'company' ? client.rut : client.ci;
    const fullName = `${client.name || '-'} ${client.lastname || ''}`.trim();

    // Actualiza los elementos de la tarjeta de información del cliente
    $('#client-id').text(client.id || '-');
    $('#client-name').text(fullName);
    $('#client-type').text(clientType);
    $('#client-doc-label').text(clientDocLabel);
    $('#client-doc').text(clientDoc || 'No disponible');

    // Muestra o oculta la información de la razón social, dependiendo del tipo de cliente
    if (client.type === 'company') {
      $('#client-company').html(`<strong>Razón Social:</strong> ${client.company_name || '-'}`);
      $('#client-company').show();
    } else {
      $('#client-company').hide();
    }

    // Muestra la tarjeta de información del cliente
    $('#client-info').show();
    $('#client-selection-container').hide();
  }

  function saveCartToSession() {
    return $.ajax({
      url: 'cart',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        cart: cart
      }
    })
      .done(function (response) {
        console.log('Carrito guardado en la sesión:', response);
      })
      .fail(function (xhr) {
        mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText);
      });
  }

  function calcularTotal() {
    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    let total = subtotal - discount;
    if (total < 0) total = 0;

    // Redondear subtotal, descuento y total a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    total = Math.round(total * 100) / 100;
    discount = Math.round(discount * 100) / 100;

    // Mostrar los valores redondeados con dos decimales y separadores de miles
    $('.subtotal').text(`${currencySymbol}${subtotal.toFixed(2).toLocaleString('es-ES')}`);
    $('.total').text(`${currencySymbol}${total.toFixed(2).toLocaleString('es-ES')}`);
    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(2).toLocaleString('es-ES')}`);
  }

  function aplicarDescuento() {
    const couponCode = $('#coupon-code').val();

    // Si no hay ningún cupón o descuento, no realizar validación
    if (!couponCode && !$('#fixed-discount').val()) {
      removeDiscount();
      return;
    }

    if (couponCode) {
      $.ajax({
        url: `${baseUrl}admin/get-coupon/${couponCode}`,
        type: 'GET',
        success: function (response) {
          if (response) {
            aplicarDescuentoPorCupon(response);
          } else {
            mostrarError('Cupón no válido o no encontrado.');
          }
        },
        error: function () {
          mostrarError('Error al aplicar el cupón.');
        }
      });
    } else {
      aplicarDescuentoFijo();
    }
  }

  function aplicarDescuentoPorCupon(couponResponse) {
    coupon = couponResponse;
    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (coupon.coupon.type === 'percentage') {
        discount = (coupon.coupon.amount / 100) * subtotal;
    } else if (coupon.coupon.type === 'fixed') {
        discount = coupon.coupon.amount;
    }

    if (discount > subtotal) {
        discount = subtotal;
    }

    discount = Math.round(discount);
    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(0)}`);

    calcularTotal();
    $('#quitarDescuento').show(); // Mostrar el botón de eliminar descuento
  }

  function aplicarDescuentoFijo() {
    const discountType = $('input[name="discount-type"]:checked').val();
    const discountValue = parseFloat($('#fixed-discount').val());

    if (!discountValue || isNaN(discountValue) || discountValue <= 0) {
      mostrarError('Por favor, ingrese un valor de descuento válido.');
      return;
    }

    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (discountType === 'percentage') {
      discount = (discountValue / 100) * subtotal;
    } else if (discountType === 'fixed') {
      discount = discountValue;
    }

    if (discount > subtotal) {
      discount = subtotal;
    }

    // Redondear el descuento a dos decimales
    discount = Math.round(discount * 100) / 100;

    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(2)}`);

    calcularTotal();
    $('#quitarDescuento').show(); // Mostrar el botón de eliminar descuento
  }


  function removeDiscount() {
    // Reiniciar variables de descuento
    discount = 0;
    coupon = null;

    // Limpiar campos de entrada relacionados con descuentos
    $('#coupon-code').val(''); // Limpiar el código de cupón
    $('#fixed-discount').val(''); // Limpiar el valor del descuento fijo

    // Actualizar la visualización del descuento a 0
    $('.discount-amount').text(`${currencySymbol}0`);

    // Recalcular el total sin descuento
    calcularTotal();

    // Ocultar el botón de eliminar descuento
    $('#quitarDescuento').hide();

    // Ocultar el mensaje de error (si hay alguno mostrado)
    ocultarError();
  }

  // Evento para el botón de "Eliminar descuento"
  $('#quitarDescuento').on('click', function () {
    removeDiscount(); // Llamar a la función para eliminar el descuento
  });

  function updateCheckoutCart() {
    let cartHtml = '';
    let subtotal = 0;

    if (!Array.isArray(cart)) {
      mostrarError('El carrito no es un array.');
      return;
    }

    cart.forEach(item => {
      const itemTotal = item.price * item.quantity;
      subtotal += itemTotal;

      // Redondear el precio del producto y el total del ítem a dos decimales
      const formattedItemPrice = (Math.round(item.price * 100) / 100).toLocaleString('es-ES', {
        minimumFractionDigits: 2
      });
      const formattedItemTotal = (Math.round(itemTotal * 100) / 100).toLocaleString('es-ES', {
        minimumFractionDigits: 2
      });

      cartHtml += `
      <li class="list-group-item d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
              <img src="${baseUrl}${item.image}" alt="${item.name}" class="img-thumbnail me-2" style="width: 50px;">
              <div>
                  <h6 class="mb-0">${item.name}</h6>
                  <small class="text-muted">Cantidad: ${item.quantity} x ${currencySymbol}${formattedItemPrice}</small>
              </div>
          </div>
          <span>${currencySymbol}${formattedItemTotal}</span>
      </li>
      `;
    });

    let total = subtotal - discount;
    if (total < 0) total = 0;

    // Redondear subtotal y total a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    total = Math.round(total * 100) / 100;

    const formattedSubtotal = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotal = total.toLocaleString('es-ES', { minimumFractionDigits: 2 });

    $('.list-group-flush').html(cartHtml);
    $('.subtotal').text(`${currencySymbol}${formattedSubtotal}`);
    $('.total').text(`${currencySymbol}${formattedTotal}`);
    calcularTotal();
  }

  $('.discount-section button').on('click', function () {
    aplicarDescuento();
  });

  function loadClients() {
    $.ajax({
      url: 'clients/json',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        const clients = response.clients;
        const clientCount = response.count;
        if (clientCount > 0) {
          $('#search-client-container').show();
        } else {
          $('#search-client-container').hide();
        }
        displayClients(clients);
      },
      error: function (xhr) {
        mostrarError('Error al obtener los clientes: ' + xhr.responseText);
      }
    });
  }

  function displayClients(clients) {
    const clientList = $('#client-list');
    clientList.empty(); // Limpiar la lista existente

    clients.forEach(client => {
      const clientType = client.type === 'company' ? 'Empresa' : 'Persona';
      const clientDoc = client.type === 'company' ? client.rut : client.ci;
      const clientDocLabel = client.type === 'company' ? 'RUT' : 'CI';
      const razonSocialText = client.company_name ? client.company_name : '-';
      const razonSocial =
        client.type === 'company' ? `<p class="client-info"><strong>Razón Social:</strong> ${razonSocialText}</p>` : '';

      const clientCard = `
            <div class="client-card card mb-2" style="border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div class="client-details">
                        <h6 class="card-title mb-1">${client.name || '-'} ${client.lastname || '-'}</h6>
                        ${razonSocial}
                        <p class="client-info"><strong>Tipo de Cliente:</strong> ${clientType}</p>
                        <p class="client-info"><strong>${clientDocLabel}:</strong> ${clientDoc ? clientDoc : 'No disponible'}</p>
                    </div>
                    <button class="btn btn-primary btn-sm btn-select-client" data-client='${JSON.stringify(client)}'>Seleccionar</button>
                </div>
            </div>
        `;

      clientList.append(clientCard);
    });

    // Event listener para el botón "Seleccionar"
    $('.btn-select-client').on('click', function () {
      const client = $(this).data('client');
      showClientInfo(client);

      saveClientToSession(client)
        .done(function () {
          loadClientFromSession();
        })
        .fail(function (xhr) {
          mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
        });
    });
  }

  $('#search-client').on('input', function () {
    const searchText = $(this).val().toLowerCase();

    // Seleccionar las tarjetas de cliente correctas
    $('#client-list .client-card').each(function () {
      const name = $(this).find('.card-title').text().toLowerCase(); // Obtener el nombre del cliente desde la tarjeta
      const ci = $(this).find('.client-info:contains("CI")').text().toLowerCase(); // Obtener CI
      const rut = $(this).find('.client-info:contains("RUT")').text().toLowerCase(); // Obtener RUT
      const company_name = $(this).find('.client-info:contains("Razón Social")').text().toLowerCase(); // Obtener Razón Social

      // Comprobar si el texto de búsqueda coincide con nombre, CI o RUT
      if (
        name.includes(searchText) ||
        ci.includes(searchText) ||
        rut.includes(searchText) ||
        company_name.includes(searchText)
      ) {
        $(this).removeClass('d-none'); // Mostrar tarjeta
      } else {
        $(this).addClass('d-none'); // Ocultar tarjeta
      }
    });
  });

  function saveClientToSession(client) {
    return $.ajax({
      url: 'client-session',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        client: client
      }
    })
      .done(function (response) {
        console.log('Cliente guardado en la sesión:', response);
      })
      .fail(function (xhr) {
        mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
      });
  }

  $('#offcanvasEnd').on('show.bs.offcanvas', function () {
    loadClients();
  });

  // Mostrar/Ocultar campos según el tipo de cliente seleccionado
  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    if (tipo === 'individual') {
        document.getElementById('ciField').style.display = 'block';
        document.getElementById('rutField').style.display = 'none';
        document.getElementById('razonSocialField').style.display = 'none';
    } else if (tipo === 'company') {
        document.getElementById('ciField').style.display = 'none';
        document.getElementById('rutField').style.display = 'block';
        document.getElementById('razonSocialField').style.display = 'block';
    }
  });

  // Guardar cliente con validaciones
  document.getElementById('guardarCliente').addEventListener('click', function () {
    const nombre = document.getElementById('nombreCliente');
    const apellido = document.getElementById('apellidoCliente');
    const tipo = document.getElementById('tipoCliente'); // Accede al elemento
    const email = document.getElementById('emailCliente');
    const ci = document.getElementById('ciCliente');
    const rut = document.getElementById('rutCliente');
    const direccion = document.getElementById('direccionCliente');
    const razonSocial = document.getElementById('razonSocialCliente');

    let hasError = false;
    clearErrors();

    if (nombre.value.trim() === '') {
      showError(nombre, 'Este campo es obligatorio');
      hasError = true;
    }

    if (apellido.value.trim() === '') {
      showError(apellido, 'Este campo es obligatorio');
      hasError = true;
    }

    // Aquí accedes al valor del campo select tipoCliente
    if (tipo.value.trim() === '') {
      showError(tipo, 'Este campo es obligatorio');
      hasError = true;
    }

    if (email.value.trim() === '') {
      showError(email, 'Este campo es obligatorio');
      hasError = true;
    }

    if (direccion.value.trim() === '') {
      showError(direccion, 'Este campo es obligatorio');
      hasError = true;
    }

    if (tipo.value === 'individual' && ci.value.trim() === '') {
      showError(ci, 'Este campo es obligatorio');
      hasError = true;
    }

    if (tipo.value === 'company') {
      if (rut.value.trim() === '') {
        showError(rut, 'Este campo es obligatorio');
        hasError = true;
      }

      if (razonSocial.value.trim() === '') {
        showError(razonSocial, 'Este campo es obligatorio');
        hasError = true;
      }
    }

    if (hasError) {
      return;
    }

    let data = {
      store_id: sessionStoreId,
      name: nombre.value.trim(),
      lastname: apellido.value.trim(),
      type: tipo.value, // Aquí se usa tipo.value para obtener el valor del select
      email: email.value.trim(),
      address: direccion.value.trim()
    };

    if (tipo.value === 'individual') {
      data.ci = ci.value.trim();
    } else if (tipo.value === 'company') {
      data.rut = rut.value.trim();
      data.company_name = razonSocial.value.trim();
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
      .catch(error => {
        mostrarError('Error al guardar el cliente: ' + error);
      });
  });


  // Función para mostrar el mensaje de error
  function showError(input, message) {
    const errorElement = document.createElement('small');
    errorElement.className = 'text-danger';
    errorElement.innerText = message;
    input.parentElement.appendChild(errorElement);
  }

  // Función para limpiar los mensajes de error anteriores
  function clearErrors() {
    const errorMessages = document.querySelectorAll('.text-danger');
    errorMessages.forEach(function (error) {
      error.remove();
    });
  }

  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    if (tipo === 'individual') {
      document.getElementById('ciField').style.display = 'block';
      document.getElementById('rutField').style.display = 'none';
      document.getElementById('razonSocialField').style.display = 'none';
    } else if (tipo === 'company') {
      document.getElementById('ciField').style.display = 'none';
      document.getElementById('rutField').style.display = 'block';
      document.getElementById('razonSocialField').style.display = 'block';
    }
  });

  $('#deselect-client').on('click', function () {
    deselectClient();
  });

  function deselectClient() {
    client = [];
    saveClientToSession(client);

    $('#client-id').text('');
    $('#client-name').text('');
    $('#client-ci').text('');
    $('#client-rut').text('');
    $('#client-info').hide();
    $('#client-selection-container').show();
  }

  loadCartFromSession();
  loadClientFromSession();
  obtenerCashRegisterLogId();
  loadStoreIdFromSession();

  function postOrder() {
    ocultarError(); // Ocultar errores previos


    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    let cashSales = 0;
    let posSales = 0;

    // Convertir los valores de texto formateados a números enteros
    const total = parseFloat($('.total').text().replace(/[^\d.-]/g, '')) || 0;
    const subtotal = parseFloat($('.subtotal').text().replace(/[^\d.-]/g, '')) || 0;

    // Validación: Verificar si el total es mayor a 600 y si hay un cliente vinculado
    if (total > 24000 && (!client || !client.id)) {
      mostrarError('Para ventas mayores a UYU24.000, es necesario tener un cliente asignado al pedido. Puede seleccionar uno existente o crear uno nuevo.');
      return;
    }


    if (paymentMethod === 'cash') {
      cashSales = total;
    } else {
      posSales = total;
    }

    // Definir docType y doc en función del tipo de cliente
    let docType = null;
    let doc = null;
    if (client) {
      if (client.type === 'company') {
        docType = 2; // RUC para empresas
        doc = client.rut;
      } else {
        docType = 3; // CI para individuos
        doc = client.ci ? client.ci : '00000000'; // Usar '12345678' si no hay CI
      }
    } else {
      docType = 3; // Por defecto, asumir CI para 'individual'
      doc = '00000000';
    }


    const orderData = {
      date: new Date().toISOString().split('T')[0],
      hour: new Date().toLocaleTimeString('it-IT'),
      cash_register_log_id: cashRegisterLogId,
      cash_sales: cashSales,
      pos_sales: posSales,
      discount: discount,
      client_id: client && client.id ? client.id : null,
      client_type: client && client.type ? client.type : 'no-client',
      products: JSON.stringify(cart),
      subtotal: subtotal,
      total: total - discount,
      notes: $('textarea').val() || '',
      store_id: sessionStoreId
    };



    // Primero, hacer el POST a pos-orders
$.ajax({
  url: `${baseUrl}admin/pos-orders`,
  type: 'POST',
  data: {
    _token: $('meta[name="csrf-token"]').attr('content'),
    ...orderData
  },
  success: function (response) {

    // Validación más robusta para los datos del cliente
    const isClientValid = client && Object.keys(client).length > 0;
    const ordersData = {
      date: orderData.date,
      time: orderData.hour,
      origin: 'physical',
      client_id: orderData.client_id,
      store_id: sessionStoreId,
      products: orderData.products,
      subtotal: orderData.subtotal,
      tax: 0,
      shipping: 0,
      coupon_id: coupon ? coupon.coupon.id : null,
      coupon_amount: coupon ? coupon.coupon.amount : 0,
      discount: orderData.discount,
      total: orderData.total,
      estimate_id: null,
      shipping_id: null,
      payment_status: 'paid',
      shipping_status: 'delivered',
      payment_method: paymentMethod,
      shipping_method: 'standard',
      preference_id: null,
      shipping_tracking: null,
      is_billed: 0,
      doc_type: docType,
      document: doc,
      name: isClientValid && client.name ? client.name : 'N/A',
      lastname: isClientValid && client.lastname ? client.lastname : 'N/A',
      address: isClientValid && client.address ? client.address : '-',
      phone: isClientValid && client.phone ? client.phone : '123456789',
      email: isClientValid && client.email ? client.email : 'no@email.com',
      cash_register_log_id: cashRegisterLogId
    };

    // Hacer POST a orders
    $.ajax({
      url: `${baseUrl}admin/orders`,
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ...ordersData
      },
      success: function (response) {
        console.log('Orden creada exitosamente:', response);  // Verificar que la orden se haya creado
        clearCartAndClient()
          .then(() => {
            return Swal.fire({
              title: 'Venta Realizada con Éxito',
              text: 'La venta se ha realizado exitosamente.',
              icon: 'success',
              showCancelButton: userHasPermission('access_orders'),
              confirmButtonText: userHasPermission('access_orders') ? 'Ver Orden' : 'Cerrar',
              cancelButtonText: 'Cerrar'
            });
          })
          .then(result => {
            if (result.isConfirmed && userHasPermission('access_orders')) {
              window.location.href = `${baseUrl}admin/orders/${response.order_uuid}/show`;
            } else {
              window.location.href = frontRoute;
            }
          })
          .catch(error => {
            console.error('Error al limpiar carrito y cliente:', error);  // Capturar errores en el proceso de limpiar carrito
            mostrarError(error);
          });
      },
      error: function (xhr) {
        console.error('Error al guardar la orden en /admin/orders:', xhr.responseText);  // Capturar errores en la creación de la orden
        mostrarError('Error al guardar la orden en /admin/orders: ' + xhr.responseText);
      }
    });
    
  },
  error: function (xhr) {
    console.error('Error al guardar en /admin/pos-orders:', xhr);  // Capturar error de pos-orders
    if (xhr.responseJSON && xhr.responseJSON.errors) {
      const errores = xhr.responseJSON.errors;
      let mensajes = '';
      for (const campo in errores) {
        mensajes += `${errores[campo].join(', ')}<br>`;
      }
      mostrarError(mensajes);
    } else {
      mostrarError(xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido');
    }
  }
});

  }

  function clearCartAndClient() {
    return new Promise((resolve, reject) => {
      // Limpiar el carrito
      saveCartToSession()
        .done(function () {
          updateCheckoutCart();

          // Limpiar el cliente
          saveClientToSession(client)
            .done(function () {
              resolve(); // Resuelve la promesa cuando ambos procesos han sido exitosos
            })
            .fail(function (xhr) {
              reject('Error al guardar el cliente en la sesión: ' + xhr.responseText);
            });
        })
        .fail(function (xhr) {
          reject('Error al guardar el carrito en la sesión: ' + xhr.responseText);
        });
    });
  }


  $('.btn-success').on('click', function () {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (paymentMethod === 'cash') {
      postOrder();
    } else {
      obtenerTokenPos().done(function (response) {
        const token = response.access_token;
        enviarTransaccionPos(token);
      }).fail(function (error) {
        console.error('Error al obtener el token del POS:', error);
      });
    }
  });

  $('#descartarVentaBtn').on('click', function () {
    client = [];
    saveClientToSession(client);
    cart = [];
    saveCartToSession();
    updateCheckoutCart();
  });

  $('#valorRecibido').on('input', function () {
    var valorRecibido = $(this).val() || 0;

    var total = parseFloat($('.total').text().replace(/[^\d.-]/g, '')) || 0;


    var vuelto = valorRecibido - total;

    // Verificar si el valor recibido es menor que el total
    if (valorRecibido < total) {
      $('#mensajeError').removeClass('d-none');
    } else {
      $('#mensajeError').addClass('d-none');
    }

    // Formatear el vuelto con separadores de miles, mínimo de 0 decimales y máximo de 2
    var formattedVuelto = vuelto.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Mostrar el vuelto formateado
    $('#vuelto').text(`${currencySymbol}${formattedVuelto}`);
  });

  $('#fixed-discount').on('input', function () {
    // Reemplazar cualquier valor que no sea un número o un punto, y limitar a dos decimales
    const value = $(this).val().replace(/[^0-9.]/g, '');

    // Usar una expresión regular para permitir hasta dos decimales
    const validValue = value.match(/^\d+(\.\d{0,2})?/);

    // Asignar el valor válido de nuevo al input
    $(this).val(validValue ? validValue[0] : '');
  });

  $('#valorRecibido').on('input', function () {
    // Reemplazar cualquier valor que no sea un número o un punto, y limitar a dos decimales
    const value = $(this).val().replace(/[^0-9.]/g, '');

    // Usar una expresión regular para permitir hasta dos decimales
    const validValue = value.match(/^\d+(\.\d{0,2})?/);

    // Asignar el valor válido de nuevo al input
    $(this).val(validValue ? validValue[0] : '');
  });

});
