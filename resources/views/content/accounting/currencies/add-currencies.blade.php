<!-- Modal Add New Currency -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1" aria-labelledby="addCurrencyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCurrencyModalLabel">Agregar Nueva Moneda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewCurrencyForm">
          <div class="mb-3">
            <label for="currency_code" class="form-label">Código de la Moneda</label>
            <input type="text" class="form-control" id="currency_code" name="code" required placeholder="Ingrese el código de la moneda">
          </div>
          <div class="mb-3">
            <label for="currency_symbol" class="form-label">Símbolo</label>
            <input type="text" class="form-control" id="currency_symbol" name="symbol" required placeholder="Ingrese el símbolo de la moneda">
          </div>
          <div class="mb-3">
            <label for="currency_name" class="form-label">Nombre de la Moneda</label>
            <input type="text" class="form-control" id="currency_name" name="name" required placeholder="Ingrese el nombre de la moneda">
          </div>
          <div class="mb-3">
            <label for="exchange_rate" class="form-label">Tipo de Cambio</label>
            <input type="number" class="form-control" id="exchange_rate" name="exchange_rate" required placeholder="Ingrese el tipo de cambio" step="0.01">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitCurrencyBtn"
          data-route="{{ route('currencies.store') }}">Guardar Moneda</button>
      </div>
    </div>
  </div>
</div>
