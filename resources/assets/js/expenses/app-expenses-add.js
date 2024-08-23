$(document).ready(function() {
  $('#addExpenseModal').on('click', '#submitExpenseBtn', function () {
    submitNewExpense();
  });

  function submitNewExpense() {
    var route = $('#submitExpenseBtn').data('route');
    var formData = {
      amount: $('#amount').val(),
      // status: $('#status').val(),
      due_date: $('#due_date').val(),
      supplier_id: $('#supplier_id').val(),
      expense_category_id: $('#expense_category_id').val(),
      store_id: $('#store_id').val()
    };
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addExpenseModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Gasto Agregado',
          text: response.message
        }).then(result => {
          // $('.datatables-expenses').DataTable().ajax.reload();
          window.location.href = `${baseUrl}admin/expense-payment-methods/${response.id}/detail`;
        });
      },
      error: function (xhr) {
        $('#addExpenseModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';
        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        }else{
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#addExpenseModal').modal('show');
        });
      }
    });
  }
});