$(document).ready(function() {
  $('#addIncomeClientModal').on('click', '#submitIncomeClientBtn', function () {
    submitNewIncome();
  });

  function submitNewIncome() {
    var route = $('#submitIncomeClientBtn').data('route');
    var formData = {
      income_name: $('#income_name').val(),
      income_description: $('#income_description').val(),
      income_date: $('#income_date').val(),
      income_amount: $('#income_amount').val(),
      payment_method_id: $('#payment_method_id').val(),
      income_category_id: $('#income_category_id').val(),
      client_id: $('#client_id').val(),
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addIncomeClientModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Ingreso Agregado',
          text: response.message
        }).then(result => {
          // window.location.href = `${baseUrl}admin/incomes-clients/${response.id}/detail`;
          location.reload();
        });
      },
      error: function (xhr) {
        $('#addIncomeClientModal').modal('hide');

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
          $('#addIncomeClientModal').modal('show');
        });
      }
    });
  }
});
