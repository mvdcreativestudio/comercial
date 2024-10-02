document.addEventListener('DOMContentLoaded', function () {
    // Inicializamos la tabla con DataTables
    var table = $('.datatables-formulas').DataTable({
        data: formulas, // Datos de las fórmulas
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'description' },
            { data: 'final_product_id' },
            {
                data: null,
                className: "text-center",
                orderable: false, 
                render: function(data, type, row) {
                    return `
                    <button class="btn btn-primary btn-view-raw-materials" data-id="${row.id}">Ver</button>`;
                }
            },  
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    return `
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><button class="dropdown-item btn-init" data-id="${row.id}">Iniciar producción</button></li>
                            <li><button class="dropdown-item btn-delete" data-id="${row.id}">Eliminar</button></li>
                        </ul>
                    </div>`;
                }
            }
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
        }
    });

    $('.select2').select2({
        placeholder: 'Selecciona un producto',
        allowClear: true
    });

    // Enviar la solicitud AJAX para agregar una nueva fórmula
    $('#addFormulaForm').on('submit', function (e) {
        e.preventDefault();

        let formData = {
            _token: window.csrfToken,
            name: $('#name').val(),
            description: $('#description').val(),
            final_product_id: $('#final_product_id').val()
        };

        $.ajax({
            type: 'POST',
            url: 'formulas',
            data: formData,
            success: function (response) {
                location.reload(); 
            },
            error: function (response) {
                alert('Ocurrió un error al agregar la fórmula');
                console.log(response);
            }
        });
    });
    
    // Lógica para mostrar u ocultar columnas
    $('.toggle-column').on('change', function () {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    $(document).on('click', '.btn-init', function () {
        var formulaId = $(this).data('id');  
    
        $.ajax({
            url: 'formula-steps-production',  
            method: 'POST',
            data: {
                formula_id: formulaId  
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  
            },
            success: function (response) {
                if (response.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin pasos',
                        text: 'Esta fórmula no tiene pasos asociados.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
    
                function showStepModal(stepIndex) {
                    var step = response[stepIndex];
                    var modalContent = `
                        <p>Materia Prima: ${step.raw_material_id}</p>
                        <p>Cantidad Requerida: ${step.quantity_required}</p>`;
                    
                    if (step.clarification) {
                        modalContent += `<p>Aclaración: ${step.clarification}</p>`;
                    }
    
                    Swal.fire({
                        title: `Paso ${step.step}`,
                        html: modalContent,
                        confirmButtonText: (stepIndex < response.length - 1) ? 'Siguiente' : 'Finalizar',
                        preConfirm: function () {
                            if (stepIndex < response.length - 1) {
                                showStepModal(stepIndex + 1);  
                            }
                        }
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel && stepIndex > 0) {
                            showStepModal(stepIndex - 1);  
                        }
                    });
                }
    
                showStepModal(0);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al obtener los pasos de la fórmula.'
                });
            }
        });
    });
    

    

    // Evento para eliminar fórmula
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        if (confirm('¿Seguro que deseas eliminar esta fórmula?')) {
            console.log("Eliminar fórmula con ID:", id);
        }
    });

    $(document).on('click', '.btn-view-raw-materials', function() {
        var formulaId = $(this).data('id');
    
        $.ajax({
            url: 'store-formula-step-id',
            method: 'POST',
            data: {
                id: formulaId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                window.location.href = 'formula-steps';
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Detalles:', xhr.responseText);
            }
        });
    });
});
