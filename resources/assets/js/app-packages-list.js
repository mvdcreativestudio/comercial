document.addEventListener('DOMContentLoaded', function () {

    $('#loadTapLabels').click(function () {
        window.location.href = "package-components"; 
    });

    $('#addPackageForm').submit(function(e) {
        e.preventDefault(); // Evita que el formulario se envíe de forma normal
  
        let formData = {
          name: $('#name').val(),
          is_sellable: $('#is_sellable').val(),
          cost: $('#cost').val(),
          price: $('#price').val(),
          unit_of_measure: $('#unit_of_measure').val(),
          size: $('#size').val(),
          stock: $('#stock').val(),
          _token: window.csrfToken // Asegúrate de incluir el token CSRF
        };
  
        $.ajax({
          url: "packages", 
          type: 'POST',
          data: formData,
          success: function(response) {
            $('#addPackageForm')[0].reset(); 
            $('.offcanvas').offcanvas('hide');
            location.reload()
          },
          error: function(xhr) {
            console.error(xhr.responseText);
          }
        });
      });

      $(document).on('click', '.btn-delete-package', function() {
        var packageId = $(this).data('id');

        eliminarPaquete(packageId);
    });

    function eliminarPaquete(packageId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción. El paquete será eliminado.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `packages/${packageId}`,  
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Eliminado!',
                            'El paquete ha sido eliminado.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(error) {
                        Swal.fire(
                            'Error!',
                            'Ocurrió un problema al intentar eliminar el paquete.',
                            'error'
                        );
                    }
                });
            }
        });
    }
});
