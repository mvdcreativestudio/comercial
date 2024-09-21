'use strict';

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Quill Editor para la descripción del producto compuesto
    const descriptionElement = document.querySelector('.comment-editor');

    if (descriptionElement) {
      const quill = new Quill(descriptionElement, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Descripción del producto compuesto',
        theme: 'snow'
      });

      // Cargar la descripción existente en el editor Quill
      const existingDescription = document.getElementById('hiddenDescription').value;
      if (existingDescription) {
        quill.root.innerHTML = existingDescription;
      }

      const form = descriptionElement.closest('form');

      if (form) {
        form.addEventListener('submit', function () {
          const hiddenInput = document.getElementById('hiddenDescription');
          if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;
          }
        });
      }
    }

    // Control de la imagen subida usando Dropzone (para el formulario de edición)
    const dropzoneElement = document.querySelector('#dropzone');
    const hiddenImageInput = document.getElementById('compositeProductImage');

    if (dropzoneElement) {
      const myDropzone = new Dropzone(dropzoneElement, {
        url: '#',
        autoProcessQueue: false,
        maxFiles: 1,
        previewsContainer: '#existingImage',
        clickable: '#btnBrowse, #dropzone',
        maxFilesize: 2,
        acceptedFiles: '.jpg,.jpeg,.png,.gif',
        init: function () {
          const dz = this;

          // Mostrar la imagen existente (si hay)
          const existingImage = document.querySelector('#existingImage img');
          if (existingImage) {
            const imgUrl = existingImage.src;
            dz.emit('addedfile', { name: 'Imagen existente' });
            dz.emit('thumbnail', { name: 'Imagen existente' }, imgUrl);
          }

          dz.on('addedfile', function (file) {
            const reader = new FileReader();

            reader.onload = function (event) {
              const arrayBuffer = event.target.result;
              const blob = new Blob([arrayBuffer], { type: file.type });
              const newFile = new File([blob], file.name, { type: file.type });

              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(newFile);

              hiddenImageInput.files = dataTransfer.files;
            };

            reader.readAsArrayBuffer(file);
          });

          dz.on('removedfile', function () {
            hiddenImageInput.value = '';
            if (dz.files.length === 0) {
              dropzoneElement.querySelector('.dz-message').style.display = 'block';
            }
          });

          dz.on('thumbnail', function (file, dataUrl) {
            document.querySelector('#existingImage').innerHTML =
              `<img src="${dataUrl}" alt="Imagen del producto compuesto" class="img-fluid" id="productImagePreview">`;
          });

          const form = dropzoneElement.closest('form');
          form.addEventListener('submit', function (event) {
            if (dz.getAcceptedFiles().length) {
              event.preventDefault();
              dz.processQueue();
              dz.on('success', function () {
                form.submit();
              });
            } else {
              form.submit();
            }
          });
        }
      });
    }

    // Inicializar select2 para los productos incluidos
    $(function () {
      const select2 = $('.select2');
      if (select2.length) {
        select2.each(function () {
          const $this = $(this);
          $this.wrap('<div class="position-relative"></div>').select2({
            dropdownParent: $this.parent(),
            placeholder: $this.data('placeholder')
          });
        });
      }

      // Botón "Descartar"
      const discardButton = document.getElementById('discardButton');
      discardButton.addEventListener('click', function () {
        let isFormFilled = Array.from(document.querySelector('form').elements).some(input => {
          if (input.type !== 'submit' && input.type !== 'button' && input.value !== '') {
            return true;
          }
        });

        if (isFormFilled) {
          Swal.fire({
            title: '¿Estás seguro?',
            text: 'Si continúas, perderás los datos no guardados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              history.back();
            }
          });
        } else {
          history.back();
        }
      });
    });

    // Evento para manejar la selección de todos los productos en el producto compuesto
    $('#selectAllProductsButton').on('click', function () {
      const productsSelect = $('#product_ids');
      productsSelect.find('option').prop('selected', true).trigger('change');
    });

    // Calcular el precio recomendado basado en los productos seleccionados
    $('#product_ids').on('change', function () {
      const selectedProductIds = $(this).val();
      let recommendedPrice = 0;

      if (selectedProductIds.length > 0) {
        const productsData = JSON.parse($('.app-ecommerce').attr('data-products'));

        selectedProductIds.forEach(productId => {
          const product = productsData.find(p => p.id == productId);
          if (product && product.price) {
            recommendedPrice += parseFloat(product.price);
          }
        });
      }
      $('#recommended_price').val(recommendedPrice.toFixed(2));
    });

    // Inicialización del campo de categorías usando select2
    $(document).ready(function () {
      $('#category-org').select2({
        placeholder: 'Seleccione la(s) categoría(s)',
        allowClear: true
      });
    });
  });
})();
