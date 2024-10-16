$(document).ready(function () {
    const form = $('#addTapLabelForm');

    form.on('submit', function (e) {
        e.preventDefault(); 

        $.ajax({
            url: 'package-components',
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': window.csrfToken 
            },
            success: function(data) {
                console.log('Success:', data);
                $('#offcanvasTapLabel').offcanvas('hide');
                            
                form[0].reset();
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // Add event listener for delete buttons
    $(document).on('click', '.btn-delete-package', function() {
        const componentId = $(this).data('id');
        deletePackageComponent(componentId);
        
    });

    function deletePackageComponent(id) {
        $.ajax({
            url: `package-components/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error deleting:', error);
                alert('Ocurrió un error al eliminar el componente. Por favor, inténtalo de nuevo.');
            }
        });
    }
});