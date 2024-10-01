'use strict';

// Comment editor (Quill initialization)
const commentEditor = document.querySelector('.comment-editor');

if (commentEditor) {
  new Quill(commentEditor, {
    modules: {
      toolbar: '.comment-toolbar'
    },
    placeholder: 'Ingrese la descripción de la categoría',
    theme: 'snow'
  });
}

// Datatable (jquery)

$(function () {
    var quillEdit = new Quill('#edit_ecommerce-category-description', {
      modules: {
          toolbar: '.comment-toolbar'
      },
      placeholder: 'Ingrese la descripción de la categoría',
      theme: 'snow'
  });

  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  // Variable declaration for category list table
  var dt_category_list_table = $('.datatables-category-list');

  //select2 for dropdowns in offcanvas

  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') //for dynamic placeholder
      });
    });
  }

  // Customers List Datatable

  if (dt_category_list_table.length) {
    var dt_category = dt_category_list_table.DataTable({
      ajax: 'product-categories/datatable',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'name' },
        { data: 'description' },
        { data: 'status'},
        { data: '' }
      ],
      columnDefs: [
        {
          // Actions
          targets: -1,
          title: 'Acciones',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded me-2"></i></button>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:void(0);" class="dropdown-item edit-record" data-id="' + full['id'] +'">Editar</a>'+
              '<a href="javascript:void(0);" class="dropdown-item text-danger delete-button" data-id="' + full['id'] + '">Eliminar</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      order: [1, 'asc'],
      dom:
        '<"card-header d-flex flex-wrap py-0"' +
        '<"me-5 ms-n2 pe-5"f>' +
        '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center mb-3 mb-sm-0 gap-3"lB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [7, 10, 20, 50, 70, 100],
      language: {
        search: '',
        searchPlaceholder: 'Buscar categoría...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ categorías',
        infoFiltered: "filtrados de _MAX_ categorías",
        paginate: {
          first: '<<',
          last: '>>',
          next: '>',
          previous: '<'
        },
      },
      buttons: [
        {
          text: '<i class="bx bx-plus me-0 me-sm-1"></i>Crear categoría',
          className: 'add-new btn btn-primary ms-2',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasEcommerceCategoryList'
          }
        }
      ],
    });
    $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
    $('.dt-buttons').addClass('d-flex flex-wrap');
    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');

    $('.toggle-column').on('change', function() {
      var column = dt_category.column($(this).attr('data-column'));
      column.visible(!column.visible());
    });
  }

  const commentEditor = new Quill('.comment-editor', {
    modules: {
        toolbar: '.comment-toolbar'
    },
    placeholder: 'Ingrese la descripción de la categoría',
    theme: 'snow'
  });

  // Generación automática de slug en el frontend con cada nuevo caracter ingresado
  $('#ecommerce-category-title').on('input', function () {
    const slugField = $('#ecommerce-category-slug');
    const slug = $(this).val().toLowerCase().replace(/[^\w\s]+/g, '').replace(/\s+/g, '-');
    slugField.val(slug);
  });



  // Guardar categoría
  $('#eCommerceCategoryListForm').submit(function(event) {
    event.preventDefault();

    var plainTextContent = commentEditor.getText();
    $('#hidden-description').val(plainTextContent);

    var statusValue = $('#statusSwitch').is(':checked') ? '1' : '0';
    $('input[name="status"]').val(statusValue);

    this.submit();
  });

  // Función para cargar datos en el formulario de edición
  $(document).on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    console.log('Record ID:', recordId);

    $.ajax({
      url: 'product-categories/' + recordId + '/get-selected',
      type: 'GET',
      success: function (data) {
        console.log('Data received:', data);
        $('#edit_ecommerce-category-title').val(data.name);
        $('#edit_ecommerce-category-slug').val(data.slug);
        $('#edit_ecommerce-category-parent-category').val(data.parent_id).trigger('change');
        $('#edit-statusSwitch').prop('checked', data.status == 1);

        quillEdit.root.innerHTML = data.description;

        $('#editCategoryButton').data('id', recordId);

        var editOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasEcommerceCategoryEdit'));
        editOffcanvas.show();
      },
      error: function (xhr) {
        console.log('Error occurred:', xhr);
        Swal.fire({
          icon: 'error',
          title: 'Error al cargar la categoría',
          text: 'No se pudo cargar la categoría. Intente nuevamente.'
        });
      }
    });
  });

  // POST para editar la categoría en la base de datos
  function submitEditProductCategory(recordId) {
    var formData = new FormData();

    formData.append('name', $('#edit_ecommerce-category-title').val());
    formData.append('slug', $('#edit_ecommerce-category-slug').val());
    formData.append('store_id', $('#edit_ecommerce-category-store').val());
    formData.append('description', quillEdit.root.innerHTML);
    formData.append('parent_id', $('#edit_ecommerce-category-parent-category').val());
    formData.append('status', $('#edit-statusSwitch').is(':checked') ? 1 : 0);

    var imageFile = $('#edit_ecommerce-category-image')[0].files[0];
    if (imageFile) {
      formData.append('image', imageFile);
    }

    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
      url: `product-categories/${recordId}/update-selected`,
      method: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        console.log('Categoría actualizada:', response);
        dt_category.ajax.reload(null, false);
        $('#offcanvasEcommerceCategoryEdit').offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: 'Categoría actualizada',
          text: 'La categoría ha sido actualizada correctamente.'
        }).then(() => window.location.reload());
      },
      error: function (xhr) {
        console.error('Error al actualizar la categoría:', xhr);
        Swal.fire({
          icon: 'error',
          title: 'Error al actualizar la categoría',
          text: 'No se pudo actualizar la categoría. Intente nuevamente.'
        });
      }
    });
  }

  // Asociar el clic del botón a la función submitEditProductCategory
  $(document).on('click', '#editCategoryButton', function () {
    var recordId = $(this).data('id');
    console.log('recordId:', recordId);
    submitEditProductCategory(recordId);
  });

  // Guardar el contenido de Quill antes de enviar el formulario de edición
  $('#editECommerceCategoryListForm').on('submit', function() {
    $('#hidden-edit-description').val(quillEdit.root.innerHTML);
  });

  // Ajustes del switch de estado
  document.addEventListener('DOMContentLoaded', function () {
    var statusSwitch = document.getElementById('statusSwitch');
    statusSwitch.value = statusSwitch.checked ? '1' : '0';

    statusSwitch.addEventListener('change', function() {
      this.value = this.checked ? '1' : '0';
    });
  });

  // Switch de estado para edición
  document.addEventListener('DOMContentLoaded', function () {
    var editStatusSwitch = document.getElementById('edit-statusSwitch');
    editStatusSwitch.value = editStatusSwitch.checked ? '1' : '0';

    editStatusSwitch.addEventListener('change', function() {
      this.value = this.checked ? '1' : '0';
    });
  });
});
