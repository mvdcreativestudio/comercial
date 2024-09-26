$(document).ready(function() {

  // Mostrar/Ocultar campos de pago parcial según el checkbox "¿Pago Parcial?"
  $('#partial_payment').on('change', function() {
    if ($(this).is(':checked')) {
      $('#partialPaymentFields').removeClass('d-none');
      $('#amount_paid').attr('required', true);
      $('#payment_method_id').attr('required', true);
      $('#currency_id_current_account').attr('required', true); // Asegura que la moneda sea requerida si hay pago parcial
    } else {
      $('#partialPaymentFields').addClass('d-none');
      $('#amount_paid').attr('required', false);
      $('#payment_method_id').attr('required', false);
      $('#currency_id_current_account').attr('required', false);
      $('#amount_paid').val('');
      $('#payment_method_id').val('');
      $('#currency_id_current_account').val(''); // Limpia el campo de moneda
    }
  });

  // Evento para el botón de guardar la cuenta corriente
  $('#submitCurrentAccountBtn').on('click', function (e) {
    e.preventDefault(); // Evita el comportamiento predeterminado del formulario
    submitNewCurrentAccount();
  });

  // Mostrar/Ocultar campos de pago completo según el checkbox "¿Está Pagado?"
  $('#is_paid').on('change', function () {
    if ($(this).is(':checked')) {
      $('#paymentFields').removeClass('d-none');
      $('#amount_paid').val($('#amount').val());
    } else {
      $('#paymentFields').addClass('d-none');
      $('#amount_paid').val('');
    }
  });

  // Mantener el monto pagado igual al monto total si está marcado como pagado
  $('#amount').on('keyup', function () {
    if ($('#partial_payment').is(':checked')) {
      $('#amount_paid').val($(this).val());
    }
  });

  // Función para enviar los datos de la nueva cuenta corriente
  function submitNewCurrentAccount() {
    // Valida si todos los campos requeridos están llenos
    if (!$('#supplier_id').val() || !$('#current_account_settings_id').val() || !$('#currency_id_current_account').val()) {
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        text: 'Por favor, completa todos los campos obligatorios.'
      });
      return;
    }

    var route = $('#submitCurrentAccountBtn').data('route');
    var formData = {
      amount: $('#amount').val(),
      supplier_id: $('#supplier_id').val(),
      current_account_settings_id: $('#current_account_settings_id').val(),
      partial_payment: $('#partial_payment').is(':checked'),
      amount_paid: $('#amount_paid').val(),
      payment_method_id: $('#payment_method_id').val(),
      // currency_id: $('#currency_id').val(),
      currency_id_current_account: $('#currency_id_current_account').val(),
      // currency_id_current_account_payment: $('#currency_id_current_account_payment').val(),
    };

    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: 'Cuenta Corriente Agregada',
          text: response.message
        }).then(result => {
          window.location.href = `${baseUrl}admin/current-account-supplier-pays/${response.id}`;
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
