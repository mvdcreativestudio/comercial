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
});
