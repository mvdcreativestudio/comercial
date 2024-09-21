$(document).ready(function () {

  // Mostrar/Ocultar campos de pago parcial según el estado del checkbox al cargar la página
  if ($('#partial_payment_edit').is(':checked')) {
    $('#partialPaymentFieldsEdit').removeClass('d-none');
    $('#amount_paid_edit').attr('required', true);
    $('#payment_method_id_edit').attr('required', true);
  } else {
    $('#partialPaymentFieldsEdit').addClass('d-none');
    $('#amount_paid_edit').attr('required', false);
    $('#payment_method_id_edit').attr('required', false);
  }

  // Mostrar/Ocultar campos de pago parcial según el checkbox "¿Pago Parcial?" cuando el usuario interactúa
  $('#partial_payment_edit').on('change', function () {
    if ($(this).is(':checked')) {
      $('#partialPaymentFieldsEdit').removeClass('d-none');
      $('#amount_paid_edit').attr('required', true);
      $('#payment_method_id_edit').attr('required', true);
    } else {
      $('#partialPaymentFieldsEdit').addClass('d-none');
      $('#amount_paid_edit').attr('required', false);
      $('#payment_method_id_edit').attr('required', false);
      $('#amount_paid_edit').val(''); // Limpia el campo si se desmarca
      $('#payment_method_id_edit').val(''); // Limpia el método de pago si se desmarca
    }
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editCurrentAccountForm').on('submit', function (e) {
    e.preventDefault();
    submitEditCurrentAccount();
  });
  
  // Función para enviar el formulario de edición
  function submitEditCurrentAccount() {
    var route = $('#updateCurrentAccountBtn').data('route');
    var formData = {
      amount: $('#amountEdit').val(),
      currency_id_current_account: $('#currency_id_current_account_edit').val(),
      client_id: $('#client_id_edit').val(),
      current_account_settings_id: $('#current_account_settings_id_edit').val(),
      partial_payment: $('#partial_payment_edit').is(':checked'),
      amount_paid: $('#amount_paid_edit').val(),
      payment_method_id: $('#payment_method_id_edit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    }

    $.ajax({
      url: `${route}`,
      type: 'PUT',
      data: formData,
      success: function () {
        Swal.fire('¡Actualizado!', 'La cuenta corriente ha sido actualizada con éxito.', 'success').then(result => {
          console.log(`${baseUrl}admin/current-account-client-sales`);
          window.location.href = `${baseUrl}admin/current-account-client-sales`;
        });
      },
      error: function (xhr) {
        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';

        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }

        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        });
      }
    });
  }
});
