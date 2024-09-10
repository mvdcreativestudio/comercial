document.addEventListener('DOMContentLoaded', function () {
  const mercadoPagoSwitch = document.getElementById('mercadoPagoSwitch');
  const mercadoPagoFields = document.getElementById('mercadoPagoFields');

  mercadoPagoSwitch.addEventListener('change', function() {
      if (this.checked) {
          mercadoPagoFields.style.display = 'block';
      } else {
          mercadoPagoFields.style.display = 'none';
      }
  });


  document.getElementById('invoicesEnabledSwitch').addEventListener('change', function() {
    var pymoFields = document.getElementById('pymoFields');
    if (this.checked) {
      pymoFields.style.display = 'block';
    } else {
      pymoFields.style.display = 'none';
    }
  });
});

