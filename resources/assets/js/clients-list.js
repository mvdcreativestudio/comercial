$(function () {
  // Declaración de variables
  var clientListContainer = $('.client-list-container');
  var ajaxUrl = clientListContainer.data('ajax-url');

  function fetchClients() {
    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      success: function (response) {
        var clients = response.data;
        clientListContainer.html(''); // Limpiar el contenedor

        if (clients.length === 0) {
          clientListContainer.html(`
            <div class="col-12">
              <div class="alert alert-info text-center">
                <i class="bx bx-info-circle"></i> No hay clientes disponibles.
              </div>
            </div>
          `);
        } else {
          clients.forEach(function (client) {
            const fullName = `${client.name} ${client.lastname}`;
            const truncatedName = fullName.length > 20 ? fullName.substring(0, 20) + '...' : fullName;

            const card = `
              <div class="col-md-6 col-lg-4 col-12 client-card-wrapper">
                <div class="client-card-container">
                  <div class="client-card position-relative">
                    <div class="client-card-header d-flex justify-content-between align-items-center">
                      <h5 class="client-name mb-0" title="${truncatedName}" data-full-name="${fullName}" data-truncated-name="${truncatedName}">${truncatedName}</h5>
                      <div class="d-flex align-items-center">
                        <span class="client-type badge ${client.type === 'company' ? 'bg-primary' : 'bg-info'} me-2">${client.type === 'company' ? 'Empresa' : 'Persona'}</span>
                        <div class="client-card-toggle">
                          <i class="bx bx-chevron-down fs-3"></i>
                        </div>
                      </div>
                    </div>
                    <div class="client-card-body" style="display: none;">
                      <div class="d-flex flex-column h-100">
                        <div>
                          <p class="client-document mb-2"><strong>${client.type === 'company' ? 'RUT:' : 'CI:'}</strong> ${client.type === 'company' ? client.rut : client.ci}</p>
                          ${client.type === 'company' ? `<p class="client-company mb-2"><strong>Razón Social:</strong> ${client.company_name}</p>` : ''}
                          <p class="client-email mb-2"><i class="bx bx-envelope me-2"></i> ${client.email}</p>
                          <p class="client-address mb-2"><i class="bx bx-map me-2"></i> ${client.address}</p>
                          <p class="client-location mb-2"><i class="bx bx-buildings me-2"></i> ${client.city}, ${client.state}</p>
                          ${client.phone ? `<p class="client-phone mb-2"><i class="bx bx-phone me-2"></i> ${client.phone}</p>` : ''}
                          ${client.website ? `<p class="client-website mb-2"><i class="bx bx-globe me-2"></i> <a href="${client.website}" target="_blank">${client.website}</a></p>` : ''}
                        </div>
                        <div class="text-end mt-auto mb-2">
                          <a href="clients/${client.id}" class="btn btn-sm btn-outline-primary view-client">
                            Ver Cliente <i class="bx bx-right-arrow-alt ms-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `;

            clientListContainer.append(card);
          });

          // Agregar evento de clic para desplegar la información
          $('.client-card').on('click', function (e) {
            if (!$(e.target).closest('.view-client').length) {
              e.preventDefault();
              e.stopPropagation();
              const $this = $(this);
              const $icon = $this.find('.client-card-toggle i');
              const $body = $this.find('.client-card-body');
              const $wrapper = $this.closest('.client-card-wrapper');
              const $name = $this.find('.client-name');

              // Alternar la tarjeta seleccionada
              $icon.toggleClass('bx-chevron-down bx-chevron-up');
              $body.slideToggle();

              // Cambiar el tamaño de la tarjeta entre col-12 y su tamaño original
              $wrapper.toggleClass('col-md-6 col-lg-4 col-12');

              // Mostrar nombre completo o truncado según si la tarjeta está abierta o cerrada
              if ($body.is(':visible')) {
                $name.text($name.data('full-name'));
              } else {
                $name.text($name.data('truncated-name'));
              }

              // Cerrar las demás tarjetas y restaurar su tamaño
              $('.client-card-body').not($body).slideUp();
              $('.client-card-wrapper').not($wrapper).removeClass('col-12').addClass('col-md-6 col-lg-4');
              $('.client-card-toggle i').not($icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');
              $('.client-card-wrapper').not($wrapper).find('.client-name').each(function() {
                $(this).text($(this).data('truncated-name'));
              });
            }
          });

          // Prevenir el cierre de la tarjeta al hacer clic en "Ver Cliente"
          $('.view-client').on('click', function(e) {
            e.stopPropagation();
          });

          // Evitar que el clic en el botón de edición propague al contenedor de la tarjeta
          $('.edit-client').on('click', function(e) {
            e.stopPropagation();
            // Aquí puedes agregar la lógica para editar el cliente
          });
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos de clientes:', error);
        clientListContainer.html(`
          <div class="col-12">
            <div class="alert alert-danger text-center">
              <i class="bx bx-error-circle"></i> Error al cargar los clientes. Por favor, intente nuevamente.
            </div>
          </div>
        `);
      }
    });
  }

  // Fetch clients on page load
  fetchClients();

  // Implementar búsqueda de clientes
  $('#searchClient').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.client-card').each(function () {
      var clientInfo = $(this).text().toLowerCase();
      $(this).closest('.col-md-6').toggle(clientInfo.includes(searchTerm));
    });
  });
});


// Validation & Phone mask
(function () {
  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    eCommerceCustomerAddForm = document.getElementById('eCommerceCustomerAddForm');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }
  // Add New customer Form Validation
  const fv = FormValidation.formValidation(eCommerceCustomerAddForm, {
    fields: {
      customerName: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname '
          }
        }
      },
      customerEmail: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'The value is not a valid email address'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });
})();

// Campo CI o RUT dependiendo si es CF o Empresa
$(document).ready(function () {
  // Escucha cambios en los botones de radio del tipo de cliente
  $('input[type=radio][name=type]').change(function () {
    clearErrors();
    if (this.value == 'individual') {
      $('#ciField').show();
      $('#ci').attr('required', true);
      $('#rutField').hide();
      $('#company_name').removeAttr('required');
      $('#rut').removeAttr('required');
      $('#ciudadAsterisk').hide();
      $('#departamentoAsterisk').hide();
    } else if (this.value == 'company') {
      $('#ciField').hide();
      $('#ci').removeAttr('required');
      $('#rutField').show();
      $('#razonSocialField').show();
      $('#company_name').attr('required', true);
      $('#rut').attr('required', true);
      $('#ciudadAsterisk').show();
      $('#departamentoAsterisk').show();
    }
  });
});


document.getElementById('guardarCliente').addEventListener('click', function (e) {
  e.preventDefault();

  // Obtener los elementos de los campos del formulario
  const nombre = document.getElementById('ecommerce-customer-add-name');
  const apellido = document.getElementById('ecommerce-customer-add-lastname');
  const tipo = document.querySelector('input[name="type"]:checked');  // Seleccionar el tipo de cliente (individual o company)
  const email = document.getElementById('ecommerce-customer-add-email');
  const ci = document.getElementById('ci');
  const rut = document.getElementById('rut');
  const razonSocial = document.getElementById('company_name');
  const direccion = document.getElementById('ecommerce-customer-add-address');
  const ciudad = document.getElementById('ecommerce-customer-add-town');
  const departamento = document.getElementById('ecommerce-customer-add-state');

  // Limpiar errores anteriores
  clearErrors();

  // Inicializar el indicador de error
  let hasError = false;

  // Validar campos obligatorios
  if (nombre.value.trim() === '') {
      showError(nombre, 'Este campo es obligatorio');
      hasError = true;
  }

  if (apellido.value.trim() === '') {
      showError(apellido, 'Este campo es obligatorio');
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

  if (tipo.value === 'individual') {
      // Validar CI para personas individuales
      if (ci.value.trim() === '') {
          showError(ci, 'Este campo es obligatorio');
          hasError = true;
      }
      // Ocultar RUT y Razón Social si es individual
      document.getElementById('rutField').style.display = 'none';
      document.getElementById('ciField').style.display = 'block';
  } else if (tipo.value === 'company') {
      // Validar Razón Social y RUT para empresas
    if (razonSocial.value.trim() === '') {
      showError(razonSocial, 'Este campo es obligatorio');
      hasError = true;
    }

    if (rut.value.trim() === '') {
        // Validar solo una vez el RUT
        if (!rut.parentElement.querySelector('.error-message')) {
            showError(rut, 'Este campo es obligatorio');
        }
        hasError = true;
    }

    if (ciudad.value.trim() === '') {
        showError(ciudad, 'Este campo es obligatorio');
        hasError = true;
    }

    if (departamento.value.trim() === '') {
        showError(departamento, 'Este campo es obligatorio');
        hasError = true;
    }

    // Mostrar campos RUT, Razón Social, Dirección, Ciudad y Departamento si es empresa
    document.getElementById('rutField').style.display = 'block';
    document.getElementById('razonSocialField').style.display = 'block';
    document.getElementById('ciField').style.display = 'none';
  }

  if (hasError) {
      return; // Detener la ejecución si hay errores
  }

  // Crear el objeto de datos a enviar
  let data = {
      name: nombre.value.trim(),
      lastname: apellido.value.trim(),
      type: tipo.value,
      email: email.value.trim(),
      address: direccion.value.trim(),
      city: ciudad.value.trim(),
      state: departamento.value.trim(),
  };

  if (tipo.value === 'individual') {
      data.ci = ci.value.trim();
  } else if (tipo.value === 'company') {
      data.rut = rut.value.trim();
      data.company_name = razonSocial.value.trim();
  }

  // Continuar con el envío del formulario
  document.getElementById('eCommerceCustomerAddForm').submit();
});

function showError(input, message) {
  const errorElement = document.createElement('small');
  errorElement.className = 'text-danger error-message';
  errorElement.innerText = message;
  input.parentElement.appendChild(errorElement);
}

function clearErrors() {
  // Eliminar solo los mensajes de error previos, no los asteriscos
  const errors = document.querySelectorAll('.text-danger.error-message');
  errors.forEach(error => error.remove());
}
