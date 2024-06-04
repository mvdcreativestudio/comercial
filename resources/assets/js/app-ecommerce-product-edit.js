document.addEventListener('DOMContentLoaded', function () {
  initQuillEditor();
  initDropzone();
  initSelect2Components();
  initFlatpickr();
  initStateSwitch();
  setupDiscardButton();
  initRepeater();
});

function initQuillEditor() {
  const commentEditorElement = document.querySelector('.comment-editor');
  if (commentEditorElement) {
    const quill = new Quill(commentEditorElement, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Descripción del producto',
      theme: 'snow'
    });

    const hiddenDesc = document.getElementById('hiddenDescription');
    if (hiddenDesc) {
      quill.root.innerHTML = hiddenDesc.value;
    }

    const form = commentEditorElement.closest('form');
    form.addEventListener('submit', function () {
      hiddenDesc.value = quill.root.innerHTML;
    });
  }
}

const existingImage = document.querySelector('#existingImage img');

if (existingImage) {
  loadExistingImage(existingImage.src);
}

function loadExistingImage(imageUrl) {
  fetch(imageUrl)
    .then(response => response.blob())
    .then(blob => {
      const newFile = new File([blob], 'existing_image.jpg', { type: blob.type });

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(newFile);

      const hiddenImageInput = document.getElementById('productImage');
      hiddenImageInput.files = dataTransfer.files;
    })
    .catch(error => console.error('Error loading existing image:', error));
}

function initDropzone() {
  const dropzoneElement = document.querySelector('#dropzone');
  const hiddenImageInput = document.getElementById('productImage');

  if (dropzoneElement) {
    const myDropzone = new Dropzone(dropzoneElement, {
      url: '#', // No se necesita URL aquí, el formulario manejará el envío
      autoProcessQueue: false,
      maxFiles: 1,
      previewsContainer: '#existingImage', // Muestra la vista previa en el contenedor existente
      clickable: '#btnBrowse, #dropzone',
      maxFilesize: 2, // Limite de 2MB
      acceptedFiles: '.jpg,.jpeg,.png,.gif',
      init: function () {
        const dz = this;

        dz.on('addedfile', function (file) {
          // Leer el archivo y actualizar el campo oculto
          const reader = new FileReader();

          reader.onload = function (event) {
            // Crear un objeto File a partir del ArrayBuffer resultante
            const arrayBuffer = event.target.result;
            const blob = new Blob([arrayBuffer], { type: file.type });
            const newFile = new File([blob], file.name, { type: file.type });

            // Crear un objeto DataTransfer para manejar los archivos
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(newFile);

            // Asignar el archivo al input oculto
            hiddenImageInput.files = dataTransfer.files;
          };

          reader.readAsArrayBuffer(file);
        });

        dz.on('removedfile', function () {
          // Vaciar el campo oculto
          hiddenImageInput.value = '';
          // Mostrar el mensaje de Dropzone si no hay archivos
          if (dz.files.length === 0) {
            dropzoneElement.querySelector('.dz-message').style.display = 'block';
          }
        });

        dz.on('thumbnail', function (file, dataUrl) {
          document.querySelector('#existingImage').innerHTML =
            `<img src="${dataUrl}" alt="Imagen del producto" class="img-fluid" id="productImagePreview">`;
        });

        const form = dropzoneElement.closest('form');
        form.addEventListener('submit', function (event) {
          if (dz.getAcceptedFiles().length) {
            // Si hay archivos en Dropzone, evita el envío automático
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
}

function initSelect2Components() {
  $('#productType')
    .select2()
    .on('change', function () {
      const isConfigurable = $(this).val() === 'configurable';
      $('#flavorsContainer').toggle(isConfigurable);
      $('#flavorsQuantityContainer').toggle(isConfigurable);
      $('#recipeCard').toggle(!isConfigurable);
    })
    .trigger('change');

  $('#category-org, #flavorsContainer select').select2({
    placeholder: 'Seleccione opciones',
    allowClear: true
  });

  $('#category-org')
    .val(function () {
      const selectedData = $(this).data('selected');
      if (selectedData) {
        try {
          return JSON.parse(selectedData);
        } catch (e) {
          console.error('Invalid JSON in data-selected attribute:', selectedData);
        }
      }
      return [];
    })
    .trigger('change');

  $('#flavorsContainer select').each(function () {
    const selectedData = $(this).data('selected');
    if (selectedData) {
      $(this).val(JSON.parse(selectedData)).trigger('change');
    }
  });

  $('#flavorsContainer select').select2({
    placeholder: 'Seleccione opciones',
    allowClear: true
  });
}

function initFlatpickr() {
  const productDateElements = document.querySelectorAll('.product-date');
  productDateElements.forEach(function (productDate) {
    flatpickr(productDate, {
      dateFormat: 'Y-m-d'
    });
  });
}

function initStateSwitch() {
  const statusSwitch = document.getElementById('statusSwitch');
  if (statusSwitch) {
    statusSwitch.checked = statusSwitch.value === '1'; // Assumes '1' is active, adjust as necessary
    statusSwitch.addEventListener('change', function () {
      this.value = this.checked ? '1' : '2'; // Toggle between '1' and '2'
    });
  }
}

function setupDiscardButton() {
  const discardButton = document.getElementById('discardButton');
  if (discardButton) {
    discardButton.addEventListener('click', function () {
      if (!confirm('Are you sure you want to discard your changes?')) {
        return false;
      }
      window.history.back();
    });
  }
}

function initRepeater() {
  const rawMaterials = JSON.parse(document.querySelector('.app-ecommerce').getAttribute('data-raw-materials'));
  const recipes = JSON.parse(document.querySelector('.app-ecommerce').getAttribute('data-recipes'));
  const repeaterList = document.querySelector('[data-repeater-list="recipes"]');

  function updateRawMaterialOptions() {
    const selectedRawMaterials = Array.from(repeaterList.querySelectorAll('.raw-material-select'))
      .map(select => select.value)
      .filter(value => value);

    repeaterList.querySelectorAll('.raw-material-select').forEach(select => {
      select.querySelectorAll('option').forEach(option => {
        option.disabled = selectedRawMaterials.includes(option.value) && option.value !== select.value;
      });
    });
  }

  function addRecipeRow(rawMaterialId = null, quantity = null, unit = '') {
    const row = document.createElement('div');
    const index = repeaterList.children.length;
    row.className = 'row mb-3';
    row.innerHTML = `
      <div class="col-4">
        <label class="form-label" for="raw-material">Materia Prima</label>
        <select class="form-select raw-material-select" name="recipes[${index}][raw_material_id]">
          <option value="">Selecciona una materia prima</option>
          ${rawMaterials
            .map(
              rawMaterial => `
              <option value="${rawMaterial.id}" data-unit="${rawMaterial.unit_of_measure}" ${rawMaterialId == rawMaterial.id ? 'selected' : ''}>${rawMaterial.name}</option>
          `
            )
            .join('')}
        </select>
      </div>
      <div class="col-3">
        <label class="form-label" for="quantity">Cantidad</label>
        <input type="number" class="form-control" name="recipes[${index}][quantity]" placeholder="Cantidad" aria-label="Cantidad" value="${quantity || ''}" ${rawMaterialId ? '' : 'disabled'}>
      </div>
      <div class="col-3 d-flex align-items-end">
        <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" value="${unit}" readonly>
      </div>
      <div class="col-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
      </div>
    `;

    repeaterList.appendChild(row);
    updateRawMaterialOptions();
  }

  if (recipes.length > 0) {
    recipes.forEach(recipe => {
      const rawMaterial = rawMaterials.find(rm => rm.id == recipe.raw_material_id);
      addRecipeRow(recipe.raw_material_id, recipe.quantity, rawMaterial ? rawMaterial.unit_of_measure : '');
    });
  } else {
    addRecipeRow();
  }

  document.querySelector('[data-repeater-create]').addEventListener('click', () => {
    addRecipeRow();
  });

  repeaterList.addEventListener('click', event => {
    if (event.target.matches('[data-repeater-delete]')) {
      event.target.closest('.row.mb-3').remove();
      updateRawMaterialOptions();

      if (repeaterList.querySelectorAll('.row.mb-3').length === 0) {
        addRecipeRow();
      }
    }
  });

  repeaterList.addEventListener('change', event => {
    if (event.target.matches('.raw-material-select')) {
      const select = event.target;
      const unitOfMeasure = select.options[select.selectedIndex].dataset.unit;
      const quantityInput = select.closest('.row.mb-3').querySelector('input[name^="recipes"][name$="[quantity]"]');
      quantityInput.disabled = !select.value;
      quantityInput.value = select.value ? quantityInput.value : '';
      select.closest('.row.mb-3').querySelector('.unit-of-measure').value = select.value ? unitOfMeasure : '';
      updateRawMaterialOptions();
    }
  });
}
