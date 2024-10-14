$(document).ready(function () {
  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editCurrentAccountForm').on('submit', function (e) {
    e.preventDefault();
    submitEditCurrentAccount();
  });
  
  // Función para enviar el formulario de edición
  function submitEditCurrentAccount() {
    var route = $('#updateCurrentAccountBtn').data('route');
    var formData = {
      current_account_id: $('#current_account_id').val(),
      total_debit: $('#total_debit').val(),
      currency_id_current_account: $('#currency_id_current_account').val(),
      client_id: $('#client_id').val(),
      supplier_id: $('#supplier_id').val(),
      current_account_settings_id: $('#current_account_settings_id').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    }

    $.ajax({
      url: `${route}`,
      type: 'PUT',
      data: formData,
      success: function () {
        Swal.fire('¡Actualizado!', 'La cuenta corriente ha sido actualizada con éxito.', 'success').then(result => {
          window.location.href = `${baseUrl}admin/current-accounts/${formData.current_account_id}/edit`;
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
