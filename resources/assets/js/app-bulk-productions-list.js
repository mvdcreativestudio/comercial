document.addEventListener('DOMContentLoaded', function () {

  // Inicializamos la tabla con DataTables
  var table = $('.datatables-bulk-productions').DataTable({
    data: bulkProductions, // Datos de las fórmulas
    columns: [
      { data: 'id' },
      { data: 'formula_id' },
      { data: 'quantity_produced' },
      { data: 'quantity_used' },
      { data: 'production_date' },
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

  // Lógica para mostrar u ocultar columnas
  $('.toggle-column').on('change', function () {
    var column = table.column($(this).attr('data-column'));
    column.visible(!column.visible());
  });


  // Evento para eliminar fórmula
  $(document).on('click', '.btn-delete', function () {
    var id = $(this).data('id');
    if (confirm('¿Seguro que deseas eliminar esta fórmula?')) {
      console.log("Eliminar fórmula con ID:", id);
    }
  });

  // Cargar las fórmulas cuando se abre el off-canvas
  $('#offcanvasProduction').on('show.bs.offcanvas', function () {
    $.ajax({
      url: 'formulas-list',
      method: 'GET',
      success: function (response) {
        let formulas = response.formulas;

        if (!formulas || formulas.length === 0) {
          Swal.fire('Advertencia', 'No se encontraron fórmulas disponibles', 'warning');
          return;
        }

        let selectFormula = $('#formula');
        selectFormula.empty(); // Limpiar las opciones previas
        selectFormula.append('<option value="">Seleccione una fórmula</option>');

        formulas.forEach(function (formula) {
          selectFormula.append(`<option value="${formula.id}">${formula.name}</option>`);
        });
      },
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar las fórmulas', 'error');
      }
    });
  });

  // Validación y envío del formulario para iniciar producción
  $('#productionForm').on('submit', function (e) {
    e.preventDefault();

    let formula = $('#formula').val();
    let quantity = $('#quantity').val();

    if (!formula || !quantity || quantity <= 0) {
      Swal.fire('Advertencia', 'Debe completar todos los campos correctamente', 'warning');
      return;
    }

    // Enviar la solicitud AJAX para iniciar producción
    $.ajax({
      url: 'start-production',  // Endpoint para iniciar producción
      method: 'POST',
      data: {
        formula_id: formula,
        quantity: quantity,
        _token: csrfToken  // Para seguridad CSRF
      },
      success: function (response) {
        if (response.success) {
              $('#offcanvasProduction').offcanvas('hide');

          showProductionDetailsModal(response.step_batch_usage);
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      },
      error: function (xhr) {
        Swal.fire('Error', 'Ocurrió un error al iniciar la producción', 'error');
      }
    });
  });

  function showProductionDetailsModal(stepBatchUsage) {
    // Limpiar el contenido del modal si se está reutilizando
    $('#productionDetailsModal .modal-body').html('');

    let currentStep = 0;
    const completedSteps = new Set();

    function renderStep(index) {
      const stepData = stepBatchUsage[index];
      const isCompleted = completedSteps.has(index);
      const isLast = index === stepBatchUsage.length - 1;
      const isCurrent = index === currentStep;

      let stepHtml = `
            <div class="card mb-3" id="step-${index}">
                <div class="card-body d-flex align-items-center" style="min-height: 100px;">
                    <div class="flex-grow-1 ${isCompleted ? 'text-decoration-line-through' : ''}">
                        <h5 class="card-title">Paso ${stepData.step}</h5>
                        <p><strong>Aclaraciones:</strong> ${stepData.clarifications || 'Ninguna aclaración disponible'}</p>
        `;

      if (stepData.batches_used && stepData.batches_used.length > 0) {
        stepHtml += '<p><strong>Lotes utilizados:</strong></p><ul>';
        stepData.batches_used.forEach(function (batch) {
          stepHtml += `<li>Lote ${batch.batch_id}: ${batch.quantity_used} unidades utilizadas</li>`;
        });
        stepHtml += '</ul>';
      }

      stepHtml += `
                    </div>
                    ${isCurrent ? `
                        <div class="ms-3">
                            <button class="btn ${isLast ? 'btn-danger' : 'btn-success'} complete-step" data-step="${index}">
                                ${isLast ? 'Finalizar' : 'Completado'}
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

      return stepHtml;
    }

    function renderAllSteps() {
      let modalContent = '<div class="container-fluid">';
      stepBatchUsage.forEach((_, index) => {
        modalContent += renderStep(index);
      });
      modalContent += '</div>';
      $('#productionDetailsModal .modal-body').html(modalContent);

      // Apply blur effect to future steps
      stepBatchUsage.forEach((_, index) => {
        if (index > currentStep) {
          $(`#step-${index}`).css({
            'filter': 'blur(5px)',
            'user-select': 'none',
            'pointer-events': 'none'
          });
        } else {
          $(`#step-${index}`).css({
            'filter': 'none',
            'user-select': 'auto',
            'pointer-events': 'auto'
          });
        }
      });
    }

    renderAllSteps();

    // Event listener for step completion
    $('#productionDetailsModal').on('click', '.complete-step', function () {
      const stepIndex = parseInt($(this).data('step'));
      completedSteps.add(stepIndex);

      if (stepIndex < stepBatchUsage.length - 1) {
        currentStep++;
        renderAllSteps();
      } else {
        // Close the modal and the offcanvas if it's the last step
        $('#productionDetailsModal').modal('hide');
        var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasProduction'));
        if (offcanvas) {
          offcanvas.hide();
        }
      }
    });

    // Mostrar el modal
    $('#productionDetailsModal').modal('show');
  }
});
