<!-- Modal Edit Entry -->
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editEntryModalLabel">Editar Asiento Contable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editEntryForm">
          <div class="mb-3">
            <label for="edit_entry_date" class="form-label">Fecha del Asiento</label>
            <input type="date" class="form-control" id="edit_entry_date" name="entry_date" required>
          </div>
          <div class="mb-3">
            <label for="edit_entry_type_id" class="form-label">Tipo de Asiento</label>
            <select class="form-select" id="edit_entry_type_id" name="entry_type_id" required>
              <option value="" selected disabled>Seleccione un tipo de asiento</option>
              @foreach($entryTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_concept" class="form-label">Concepto</label>
            <input type="text" class="form-control" id="edit_concept" name="concept" required placeholder="Ingrese el concepto del asiento">
          </div>
          <div class="mb-3">
            <label for="edit_currency_id" class="form-label">Moneda</label>
            <select class="form-select" id="edit_currency_id" name="currency_id" required>
              <option value="" selected disabled>Seleccione una moneda</option>
              @foreach($currencies as $currency)
                <option value="{{ $currency->id }}">{{ $currency->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit_details" class="form-label">Detalles del Asiento</label>
            <div id="editEntryDetails">
              <!-- Aquí se cargarán dinámicamente los detalles del asiento existente -->
            </div>
            <button type="button" class="btn btn-secondary" id="addEditEntryDetail">Agregar Detalle</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateEntryBtn" data-route="{{ route('entries.update', ['entry' => ':id']) }}">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
