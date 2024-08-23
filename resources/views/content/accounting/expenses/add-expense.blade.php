<!-- Modal Add New Expense -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExpenseModalLabel">Agregar Nuevo Gasto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewExpenseForm">
          <div class="mb-3">
            <label for="amount" class="form-label">Monto</label>
            <input type="number" class="form-control" id="amount" name="amount" required placeholder="Ingrese el monto del gasto">
          </div>
          {{-- <div class="mb-3">
            <label for="status" class="form-label">Estado</label>
            <select class="form-select" id="status" name="status" required>
              <option value="" selected disabled>Seleccione un estado</option>
              @foreach($expenseStatus as $value => $name)
                  <option value="{{ $value }}">{{ $name }}</option>
              @endforeach
          </select> 
          </div> --}}
          <div class="mb-3">
            <label for="due_date" class="form-label">Fecha de Vencimiento</label>
            <input type="date" class="form-control" id="due_date" name="due_date" required>
          </div>
          <div class="mb-3">
            <label for="supplier_id" class="form-label">Proveedor</label>
            <select class="form-select" id="supplier_id" name="supplier_id" required>
              <!-- Options should be populated dynamically -->
              <option value="" selected disabled>Seleccione un proveedor</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="expense_category_id" class="form-label">Categoría de Gasto</label>
            <select class="form-select" id="expense_category_id" name="expense_category_id" required>
              <!-- Options should be populated dynamically -->
              <option value="" selected disabled>Seleccione una categoría</option>
              @foreach($expenseCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="store_id" class="form-label">Tienda</label>
            <select class="form-select" id="store_id" name="store_id" required>
              <!-- Options should be populated dynamically -->
              <option value="" selected disabled>Seleccione una tienda</option>
              @foreach($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
              @endforeach
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitExpenseBtn"
          data-route="{{ route('expenses.store') }}">Guardar Gasto</button>
      </div>
    </div>
  </div>
</div>