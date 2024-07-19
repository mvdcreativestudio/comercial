'use strict';

// Definir dt_user en el ámbito global
var dt_user;

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

  // Variable declaration for table
  var dt_user_table = $('.datatables-users'),
    select2 = $('.select2'),
    userView = baseUrl + 'users', // Updated URL for viewing user
    statusObj = {
      1: { title: 'Pending', class: 'bg-label-warning' },
      2: { title: 'Active', class: 'bg-label-success' },
      3: { title: 'Inactive', class: 'bg-label-secondary' }
    };

  // Users datatable
  if (dt_user_table.length) {
    dt_user = dt_user_table.DataTable({
      ajax: baseUrl + 'admin/users/datatable',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'roles' },
        { data: 'store_name' },
        { data: 'action' },
      ],
      columnDefs: [
        {
          // Render store name instead of store_id
          targets: 4,
          render: function (data, type, full, meta) {
            return full.store_name; // Assuming the server returns the store name in 'store_name'
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Acciones',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded me-2"></i></button>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item edit-record" data-id="' +
              full.id +
              '">Editar</a>' +
              '<a href="javascript:;" class="dropdown-item delete-record" data-id="' +
              full.id +
              '">Eliminar</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      order: [[1, 'desc']],
      dom:
        '<"row mx-2"' +
        '<"col-md-6"l>' +
        '<"col-md-6 d-flex justify-content-end"B>>' + // Add the button to the table header
        't' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      buttons: [
        {
          text: 'Crear Usuario',
          className: 'btn btn-primary mt-1',
          action: function (e, dt, node, config) {
            console.log('Botón Crear Usuario clicado'); // Verificar si el botón está clicando
            $('#addNewUserForm')[0].reset(); // Resetear el formulario
            $('#addNewUserForm').attr('data-id', ''); // Limpiar el ID del formulario
            $('#offcanvasAddUser').offcanvas('show');
          }
        }
      ],
      language: {
        search: '',
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoFiltered: "filtrados de _MAX_ productos",
        paginate: {
          first: '<<',
          last: '>>',
          next: '>',
          previous: '<'
        },
        emptyTable: 'No hay registros disponibles',
        pagingType: "full_numbers",
        dom: 'Bfrtip',
        renderer: "bootstrap"
      },
    });
    // To remove default btn-secondary in export buttons
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  }

  // Delete Record
  $('.datatables-users tbody').on('click', '.delete-record', function () {
    var userId = $(this).data('id');
    Swal.fire({
      title: '¿Estás seguro?',
      text: "No podrás revertir esto!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminarlo!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: baseUrl + 'admin/users/' + userId,
          method: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function () {
            dt_user.row($(`.delete-record[data-id="${userId}"]`).parents('tr')).remove().draw();
            Swal.fire({
              icon: 'success',
              title: 'Usuario eliminado',
              text: 'El usuario ha sido eliminado con éxito.',
              timer: 2000,
              showConfirmButton: false
            });
          },
          error: function (xhr, status, error) {
            console.error('Error deleting user:', error);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'No se pudo eliminar el usuario. Intente nuevamente.',
            });
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-users tbody').on('click', '.edit-record', function () {
    var userId = $(this).data('id');
    $.ajax({
      url: baseUrl + 'admin/users/' + userId,
      method: 'GET',
      success: function (response) {
        // Llenar el formulario con los datos del usuario
        $('#add-user-name').val(response.name);
        $('#add-user-email').val(response.email);
        $('#add-user-password').val(''); // Vaciar el campo de contraseña
        $('#add-user-password-confirmation').val(''); // Vaciar el campo de confirmación de contraseña
        $('#addNewUserForm').attr('data-id', userId); // Agregar el ID al formulario
        $('#offcanvasAddUser').offcanvas('show'); // Mostrar el modal
      },
      error: function (xhr, status, error) {
        console.error('Error fetching user data:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudieron obtener los datos del usuario. Intente nuevamente.',
        });
      }
    });
  });
});

// Validation & Phone mask
(function () {
    addNewUserForm = document.getElementById('addNewUserForm');

  // Add New User Form Validation
  const fv = FormValidation.formValidation(addNewUserForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Por favor ingrese el nombre completo'
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Por favor ingrese el correo electrónico'
          },
          emailAddress: {
            message: 'El valor no es una dirección de correo electrónico válida'
          }
        }
      },
      password: {
        validators: {
          notEmpty: {
            message: 'Por favor ingrese la contraseña',
            // Hacer que el campo de contraseña sea opcional si es una actualización
            enabled: function(validator, $field, options) {
              return !$('#addNewUserForm').attr('data-id');
            }
          }
        }
      },
      password_confirmation: {
        validators: {
          notEmpty: {
            message: 'Por favor confirme la contraseña',
            // Hacer que el campo de confirmación de contraseña sea opcional si es una actualización
            enabled: function(validator, $field, options) {
              return !$('#addNewUserForm').attr('data-id');
            }
          },
          identical: {
            compare: function () {
              return document.getElementById('add-user-password').value;
            },
            message: 'Las contraseñas no coinciden'
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
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  $('#addNewUserForm').on('submit', function (e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var userId = $(this).attr('data-id');
    var url = userId ? baseUrl + 'admin/users/' + userId : baseUrl + 'admin/users';
    var method = userId ? 'PUT' : 'POST';

    console.log('Formulario enviado'); // Verificar si el formulario se envía

    $.ajax({
      url: url,
      method: method,
      data: formData,
      success: function (response) {
        console.log('Usuario creado/actualizado con éxito'); // Verificar la respuesta de éxito
        dt_user.ajax.reload(); // Recargar la tabla
        $('#offcanvasAddUser').offcanvas('hide'); // Cerrar el modal
        Swal.fire({
          icon: 'success',
          title: 'Usuario ' + (userId ? 'actualizado' : 'creado'),
          text: 'El usuario ha sido ' + (userId ? 'actualizado' : 'creado') + ' con éxito.',
          timer: 2000,
          showConfirmButton: false
        });
      },
      error: function (xhr, status, error) {
        console.error('Error adding/updating user:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo ' + (userId ? 'actualizar' : 'crear') + ' el usuario. Intente nuevamente.',
        });
      }
    });
  });

})();
