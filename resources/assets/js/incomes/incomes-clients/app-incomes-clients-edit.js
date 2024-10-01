$(document).ready(function () {
  // Abrir modal para editar ingreso
  $('.datatables-incomes-clients tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    console.log(recordId);
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editIncomeClientForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#submitEditIncomeClientBtn').data('id');
    submitEditIncome(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editIncomeClientModal').on('click', '#submitEditIncomeClientBtn', function (e) {
    e.preventDefault();
    $('#editIncomeClientForm').submit();
  });

  // Preparar modal de edición con los datos del ingreso
  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `incomes-clients/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#edit_income_name').val(data.income_name);
        $('#edit_income_description').val(data.income_description);
        $('#edit_income_date').val(moment(data.income_date).format('YYYY-MM-DD'));
        $('#edit_income_amount').val(data.income_amount);
        $('#edit_payment_method_id').val(data.payment_method_id);
        $('#edit_income_category_id').val(data.income_category_id);
        $('#edit_client_id').val(data.client_id);

        // Mostrar el modal
        $('#editIncomeClientModal').modal('show');
        $('#submitEditIncomeClientBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  // Función para enviar los datos editados
  function submitEditIncome(recordId) {
    var formData = {
      income_name: $('#edit_income_name').val(),
      income_description: $('#edit_income_description').val(),
      income_date: $('#edit_income_date').val(),
      income_amount: $('#edit_income_amount').val(),
      payment_method_id: $('#edit_payment_method_id').val(),
      income_category_id: $('#edit_income_category_id').val(),
      client_id: $('#edit_client_id').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `incomes-clients/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editIncomeClientModal').modal('hide');
        $('.datatables-incomes-clients').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'El ingreso ha sido actualizado con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editIncomeClientModal').modal('hide');

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
        }).then(result => {
          $('#editIncomeClientModal').modal('show');
        });
      }
    });
  }
});
