document.addEventListener('DOMContentLoaded', function () {
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
});
