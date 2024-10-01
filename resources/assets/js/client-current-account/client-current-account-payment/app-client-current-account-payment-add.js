$(document).ready(function () {
  // Evento para el botón de guardar el pago
  $('#submitPaymentBtn').on('click', function (e) {
    e.preventDefault(); // Evita el comportamiento predeterminado del formulario
    submitNewPayment();
  });

  // Función para enviar los datos del nuevo pago
  function submitNewPayment() {
    // Valida si todos los campos requeridos están llenos
    if (
      !$('#client_id').val() ||
      !$('#payment_amount').val() ||
      !$('#payment_method_id').val() ||
      !$('#payment_date').val()
    ) {
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        text: 'Por favor, completa todos los campos obligat orios.'
      });
      return;
    }

    // Obtener la ruta de acción del formulario
    var route = $('#addNewPaymentForm').attr('action');

    // Recopilar los datos del formulario
    var formData = {
      current_account_id: $('input[name="current_account_id"]').val(),
      client_id: $('input[name="client_id"]').val(),
      payment_amount: $('#payment_amount').val(),
      payment_method_id: $('#payment_method_id').val(),
      payment_date: $('#payment_date').val(),
      _token: $('meta[name="csrf-token"]').attr('content') // Token CSRF
    };

    // Realizar la petición AJAX
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
          title: 'Pago Registrado',
          text: 'El pago ha sido agregado correctamente.'
        }).then(result => {
          window.location.href = `${baseUrl}admin/current-account-client-payments/${response.current_account_id}`; // Redirige a la lista de cuentas corrientes
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
