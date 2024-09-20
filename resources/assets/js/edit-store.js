document.addEventListener('DOMContentLoaded', function () {
  // Seleccionar los elementos relevantes
  const mercadoPagoSwitch = document.getElementById('mercadoPagoSwitch');
  const mercadoPagoFields = document.getElementById('mercadoPagoFields');
  const ecommerceSwitch = document.getElementById('ecommerceSwitch');
  const ecommerceFields = document.getElementById('ecommerceFields');
  const invoicesEnabledSwitch = document.getElementById('invoicesEnabledSwitch');
  const pymoFields = document.getElementById('pymoFields');

  // Manejar la visibilidad inicial de los campos de PyMo
  pymoFields.style.display = invoicesEnabledSwitch.checked ? 'block' : 'none';

  // Evento para el switch de habilitar facturación
  invoicesEnabledSwitch.addEventListener('change', function() {
    pymoFields.style.display = this.checked ? 'block' : 'none';
  });

  // Verificar el estado inicial de los campos de MercadoPago
  const mercadoPagoInitialState = mercadoPagoSwitch.checked;
  mercadoPagoFields.style.display = mercadoPagoInitialState ? 'block' : 'none';

  // Manejar el cambio en el switch de MercadoPago
  mercadoPagoSwitch.addEventListener('change', function () {
    if (mercadoPagoInitialState && !this.checked) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se perderán los datos de la vinculación con MercadoPago y deberá ser realizada nuevamente',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          mercadoPagoFields.style.display = 'none';
        } else {
          mercadoPagoSwitch.checked = true;
        }
      });
    } else {
      mercadoPagoFields.style.display = this.checked ? 'block' : 'none';
    }
  });

  // Verificar el estado inicial de los campos de Ecommerce
  const ecommerceInitialState = ecommerceSwitch.checked;
  ecommerceFields.style.display = ecommerceInitialState ? 'block' : 'none';

  // Manejar el cambio en el switch de Ecommerce
  ecommerceSwitch.addEventListener('change', function () {
    if (ecommerceInitialState && !this.checked) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se perderán los datos de la vinculación con Ecommerce y deberá ser realizada nuevamente',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          ecommerceFields.style.display = 'none';
        } else {
          ecommerceSwitch.checked = true;
        }
      });
    } else {
      ecommerceFields.style.display = this.checked ? 'block' : 'none';
    }
  });
});
