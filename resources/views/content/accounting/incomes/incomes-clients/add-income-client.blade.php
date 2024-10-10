<!-- Modal Add New Income -->
<div class="modal fade" id="addIncomeClientModal" tabindex="-1" aria-labelledby="addIncomeClientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addIncomeClientModalLabel">Agregar Nuevo Ingreso Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewIncomeForm">
          <!-- Nombre del Ingreso -->
          <div class="mb-3">
            <label for="income_name" class="form-label">Nombre del Ingreso</label>
            <input type="text" class="form-control" id="income_name" name="income_name" required placeholder="Ingrese el nombre del ingreso">
          </div>

          <!-- Descripción del Ingreso -->
          <div class="mb-3">
            <label for="income_description" class="form-label">Descripción del Ingreso</label>
            <textarea class="form-control" id="income_description" name="income_description" placeholder="Ingrese una descripción del ingreso (opcional)"></textarea>
          </div>

          <!-- Fecha del Ingreso -->
          <div class="mb-3">
            <label for="income_date" class="form-label">Fecha del Ingreso</label>
            <input type="date" class="form-control" id="income_date" name="income_date" required>
          </div>

          <!-- Importe del Ingreso -->
          <div class="mb-3">
            <label for="income_amount" class="form-label">Importe</label>
            <input type="number" class="form-control" id="income_amount" name="income_amount" required placeholder="Ingrese el importe del ingreso">
          </div>

          <!-- Método de Pago -->
          <div class="mb-3">
            <label for="payment_method_id" class="form-label">Método de Pago</label>
            <select class="form-select" id="payment_method_id" name="payment_method_id" required>
              <option value="" selected disabled>Seleccione un método de pago</option>
              @foreach($paymentMethods as $method)
                <option value="{{ $method->id }}">{{ $method->description }}</option>
              @endforeach
            </select>
          </div>

          <!-- Categoría del Ingreso -->
          <div class="mb-3">
            <label for="income_category_id" class="form-label">Categoría del Ingreso</label>
            <select class="form-select" id="income_category_id" name="income_category_id" required>
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($incomeCategories as $category)
                <option value="{{ $category->id }}">{{ $category->income_name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Cliente -->
          <div class="mb-3">
            <label for="client_id" class="form-label">Cliente</label>
            <select class="form-select" id="client_id" name="client_id" required>
              <option value="" selected disabled>Seleccione un cliente</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitIncomeClientBtn" data-route="{{ route('incomes-clients.store') }}">Guardar Ingreso</button>
      </div>
    </div>
  </div>
</div>
