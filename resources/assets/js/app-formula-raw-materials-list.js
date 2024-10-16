document.addEventListener('DOMContentLoaded', function () {
    var table;

    function initDataTable() {
        table = $('.datatables-formula-steps').DataTable({
            "order": [[ 0, "desc" ]],
            data: purchaseOrderItems,
            columns: [
                { data: 'id' },
                { data: 'formula_id' },
                { data: 'raw_material_id' },
                { data: 'quantity_required' },
                { data: 'clarification'},
                { data: 'step'},
                {
                    data: null,
                    className: "text-center",
                    orderable: false, 
                    render: function(data, type, row) {
                        return `
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><button class="dropdown-item btn-edit" data-id="${row.id}">Editar</button></li>
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
    }

    // Actualizar la card de total de pasos
    function updateTotalSteps() {
        const totalSteps = purchaseOrderItems.length;
        document.querySelector('.total-steps').textContent = totalSteps;
    }

    // Actualizar la card de materias primas diferentes
    function updateTotalRawMaterials() {
        const rawMaterials = purchaseOrderItems.map(item => item.raw_material_id);
        const uniqueRawMaterials = new Set(rawMaterials);
        document.querySelector('.total-raw-material').textContent = uniqueRawMaterials.size;
    }

    initDataTable();
    updateTotalSteps();
    updateTotalRawMaterials();
    
    
    // Manejo de la visibilidad de columnas
    $('.toggle-column').on('change', function(e) {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('csvFile');
    const uploadButton = document.getElementById('uploadButton');
    const form = document.getElementById('csvUploadForm');
    const fileNameDisplay = document.createElement('p');
    fileNameDisplay.classList.add('mt-3');
    fileNameDisplay.style.display = 'none';
    dropzone.appendChild(fileNameDisplay);
    const removeFileButton = document.createElement('button');
    removeFileButton.classList.add('btn', 'btn-outline-danger', 'mt-2');
    removeFileButton.textContent = 'Quitar archivo seleccionado';
    removeFileButton.style.display = 'none';
    dropzone.appendChild(removeFileButton);
        
    uploadButton.addEventListener('click', () => {
        fileInput.click();
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-primary');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-primary');
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            fileNameDisplay.textContent = `Archivo seleccionado: ${fileInput.files[0].name}`;
            fileNameDisplay.style.display = 'block';
            uploadButton.style.display = 'none'; 
            removeFileButton.style.display = 'block'; 
        }
    });
    
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary');
        fileInput.files = e.dataTransfer.files;
        if (fileInput.files.length > 0) {
            fileNameDisplay.textContent = `Archivo seleccionado: ${fileInput.files[0].name}`;
            fileNameDisplay.style.display = 'block';
            uploadButton.style.display = 'none'; 
            removeFileButton.style.display = 'block'; 
        }
    });
    
    removeFileButton.addEventListener('click', () => {
        fileInput.value = ''; // Reinicia el valor del input de archivo
        fileNameDisplay.style.display = 'none'; // Oculta el nombre del archivo
        uploadButton.style.display = 'block'; // Muestra el botón "Subir archivo"
        removeFileButton.style.display = 'none'; // Oculta el botón "Quitar archivo"
    });

    $(form).on('submit', function(e) {
        e.preventDefault(); // Previene el comportamiento por defecto del formulario

        var formData = new FormData(this);

        $.ajax({
            url: 'formula-steps-csv',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken // Token CSRF para la protección
            },
            success: function(data) {
                if (data.success) {
                    window.location.reload(); // Recargar la página si el archivo se carga correctamente
                } else {
                    alert('Error al cargar el archivo: ' + data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            }
        });
    });

    $('#repeater').repeater({
        initEmpty: false,
        show: function () {
            $(this).slideDown();
            populateRawMaterialsSelect($(this).find('select[name="raw-material"]'));
        },
        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        },
        ready: function (setIndexes) {
            $('select[name="raw-material"]').each(function() {
                populateRawMaterialsSelect($(this));
            });
            $(this).find('select[name="raw-material"]').each(function() {
                populateRawMaterialsSelect($(this));
            });
        },
        isFirstItemUndeletable: false
    });

    function populateRawMaterialsSelect(selectElement) {
        selectElement.empty();
        selectElement.append('<option value="">Seleccionar materia prima</option>');
        
        for (var id in rawMaterialsData) {
            selectElement.append(`<option value="${id}">${rawMaterialsData[id].name}</option>`);
        }
    }
    
    $(document).on('click', '.addStepsModal', function () {
        $('#stepsModal').modal('show');
    });

    let rawMaterialsData = {}; // Para almacenar los materiales y sus unidades de medida

    // Cargar materias primas cuando se abre el modal
    $('#stepsModal').on('show.bs.modal', function () {
        $.ajax({
            url: 'purchase-orders-items-raw-materials',
            method: 'GET',
            success: function (response) {
                var rawMaterialSelect = $('#raw-material');
                rawMaterialSelect.empty(); // Limpiar opciones previas
                rawMaterialSelect.append('<option value="">Seleccionar materia prima</option>');

                // Iterar sobre la respuesta y añadir las opciones al select
                $('select[name="raw-material"]').each(function () {
                    populateRawMaterialsSelect($(this));
                });
                response.forEach(function (item) {
                    rawMaterialsData[item.id] = item.unit_of_measure; // Guardar el unit_of_measure
                    rawMaterialSelect.append(`<option value="${item.id}">${item.name}</option>`);
                });
            },
            error: function () {
                alert('Error al cargar las materias primas.');
            }
        });
    });

    // Detectar cambio en el selector de materias primas
    $('#raw-material').change(function () {
        var selectedRawMaterial = $(this).val();
        var quantityInput = $('.lot-quantity');
        var unitOfMeasureSpan = $('#unit-of-measure');

        if (selectedRawMaterial) {
            // Habilitar el campo de cantidad si hay una materia prima seleccionada
            quantityInput.prop('disabled', false);

            // Mostrar la unidad de medida correspondiente
            var unitOfMeasure = rawMaterialsData[selectedRawMaterial];
            unitOfMeasureSpan.text(unitOfMeasure);
        } else {
            // Deshabilitar el campo de cantidad si no se selecciona materia prima
            quantityInput.prop('disabled', true);
            unitOfMeasureSpan.text('--'); // Resetear la unidad de medida
        }
    });

    $(document).on('click', '#save-lots', function() {
        // Recoger los datos del formulario
        var stepsData = $('#steps-form').serializeArray();
        
        // Transformar los datos para que se ajusten a la estructura esperada por el servidor
        var formattedData = {};
        stepsData.forEach(function(item) {
            if (!formattedData[item.name]) {
                formattedData[item.name] = [];
            }
            formattedData[item.name].push(item.value);
        });
    
        // Enviar los datos a la ruta storeMultiple
        $.ajax({
            url: 'formula-steps-multiple', 
            method: 'POST',
            data: {
                steps: formattedData, // Datos del formulario
                _token: window.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#stepsModal').modal('hide'); // Cierra el modal
                    location.reload();
                } else {
                    alert('Hubo un error al guardar los pasos.'); // Manejo de errores
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al enviar datos:', error);
                alert('Error al enviar datos. Por favor, intenta de nuevo.');
            }
        });
    });

    
});
