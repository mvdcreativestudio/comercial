@extends('layouts/layoutMaster')

@section('title', 'Detalle de la venta')

@if (request()->is('*/pdf'))
    <!-- Cargar estilos CSS directamente -->
    <link rel="stylesheet" href="{{ public_path('path/to/datatables-bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ public_path('path/to/datatables-responsive-bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ public_path('path/to/datatables-buttons-bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ public_path('path/to/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ public_path('path/to/form-validation.css') }}">
    <link rel="stylesheet" href="{{ public_path('path/to/select2.css') }}">
@else
    @section('vendor-style')
    @vite([
      'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
      'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
      'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
      'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
      'resources/assets/vendor/libs/@form-validation/form-validation.scss',
      'resources/assets/vendor/libs/select2/select2.scss'
    ])
    @endsection

    @section('vendor-script')
    @vite([
      'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
      'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
      'resources/assets/vendor/libs/cleavejs/cleave.js',
      'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
      'resources/assets/vendor/libs/@form-validation/popular.js',
      'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
      'resources/assets/vendor/libs/@form-validation/auto-focus.js',
      'resources/assets/vendor/libs/select2/select2.js'
    ])
    @endsection

    @section('page-script')
    @vite([
      'resources/assets/js/app-ecommerce-order-details.js',
    ])
    <script>
      window.orderProducts = @json($products);
      window.baseUrl = "{{ url('') }}/";
      window.currencySymbol = "{{ $settings->currency_symbol }}"
    </script>
    @endsection
@endif

@section('content')

@php
use Carbon\Carbon;
Carbon::setLocale('es');
$paymentStatusTranslations = [
  'paid' => 'Pagado',
  'pending' => 'Pendiente',
  'failed' => 'Fallido',
];

$shippingStatusTranslations = [
  'pending' => 'Pendiente',
  'shipped' => 'Enviado',
  'delivered' => 'Entregado',
  'pickup' => 'Retira en tienda',
];

$changeTypeTranslations = [
  'payment' => 'Pago',
  'shipping' => 'Envío',
  'status' => 'Estado',
];
@endphp

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Detalles de la venta
</h4>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 card p-3">
  <div class="d-flex flex-column justify-content-center">
    <h6 class="mb-1 mt-3">Venta #{{ $order->id }}
      @if($order->payment_status === 'paid')
        <span class="badge bg-label-info me-2 ms-2">Pago</span>
      @elseif($order->payment_status === 'pending')
        <span class="badge bg-label-danger me-2 ms-2">Pago pendiente</span>
      @elseif($order->payment_status === 'failed')
        <span class="badge bg-label-danger me-2 ms-2">Pago fallido</span>
      @endif
      @if($order->shipping_status === 'pending' && $order->shipping_method !== 'pickup')
        <span class="badge bg-label-warning">No enviado</span>
      @elseif($order->shipping_method === 'pickup')
        <span class="badge bg-label-primary">Retira en Empresa</span>
      @elseif($order->shipping_status === 'shipped')
        <span class="badge bg-label-success">Enviado</span>
      @elseif($order->shipping_status === 'delivered')
        <span class="badge bg-label-info">Entregado</span>
      @endif
    </h6>
    <h6 class="card-title mb-1 mt-1">Empresa:
      <span class="mb-1 me-2 ms-2">{{ $order->store->name }}</span>
    </h6>
    <h6 class="card-title mb-1 mt-1">Método de pago:
      @if($order->payment_method === 'card')
        <span class="badge bg-label-primary me-2 ms-2">MercadoPago</span>
      @elseif($order->payment_method === 'cash')
        <span class="me-2 ms-2">Efectivo</span>
      @elseif($order->payment_method === 'debit')
        <span class="me-2 ms-2">Débito</span>
      @elseif($order->payment_method === 'credit')
        <span class="me-2 ms-2">Crédito</span>
      @endif
    </h6>
    @if($store->invoices_enabled)
    <!-- Mostrar si la venta ha sido facturada -->
    <h6 class="card-title mb-1 mt-1">Estado de la Facturación:
      @if($order->is_billed)
        <span class="badge bg-label-success me-2 ms-2">Facturado</span>
      @else
        <span class="badge bg-label-danger me-2 ms-2">No Facturado</span>
      @endif
    </h6>
    @endif
    <!-- Mostrar el vendedor -->
    <h6 class="card-title mb-1 mt-1">Vendido por:
      @if($order->cashRegisterLog !== null)
        <span class="me-2 ms-2">{{ ucwords($order->cashRegisterLog->cashRegister->user->name) }}</span>
      @else
        <span class="me-2 ms-2">Sin registro</span>
      @endif
    </h6>
    <p class="text-body mb-1">{{ date('d/m/Y', strtotime($order->date)) }} - {{ $order->time }}</p>

  </div>
  <div class="d-flex align-content-center flex-wrap gap-2">
    <a href="{{ route('orders.pdf', ['order' => $order->uuid]) }}?action=print" target="_blank" onclick="window.open(this.href, 'print_window', 'left=100,top=100,width=800,height=600').print(); return false;">
        <button class="btn btn-primary">Imprimir</button>
    </a>
    @if(!$order->is_billed && $store->invoices_enabled)
      <button type="button" class="btn btn-label-info" data-bs-toggle="modal" data-bs-target="#emitirFacturaModal">
        Emitir Factura
      </button>
    @endif
    <a href="{{ route('orders.pdf', ['order' => $order->uuid]) }}?action=download" class="btn btn-label-primary">Descargar Recibo</a>
    <button class="btn btn-label-danger delete-order">Eliminar</button>
  </div>
</div>

<!-- Formulario para actualizar el estado del pago y envío -->
<div class="row mb-4">
  <div class="col-12">

  </div>
</div>

<!-- Order Details Table -->
<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Detalles de la venta</h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="datatables-order-details table" data-order-id="{{ $order->id }}">
          <thead>
            <tr>
              <th class="w-25">imagen</th>
              <th class="w-50">productos</th>
              <th class="w-25">precio</th>
              <th class="w-25">cantidad</th>
              <th>total</th>
            </tr>
          </thead>
        </table>
          <div class="d-flex justify-content-end align-items-center m-3 mb-2 p-1">
            @if($order->coupon_id !== null)
              <div class="d-flex align-items-center me-3">
                <span class="text-heading">Cupón utilizado:</span>
                <span class="badge bg-label-dark">{{$order->coupon->code}}</span>
              </div>
            @endif
            <div class="order-calculations">
              <div class="d-flex justify-content-between mb-2">
                <span class="w-px-100">Subtotal:</span>
                <span class="text-heading">{{ $settings->currency_symbol }}{{ $order->subtotal }}</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                @if($order->discount !== null && $order->discount !== 0)
                  <span class="w-px-100">Descuento:</span>
                  @if($order->discount !== null && $order->discount !== 0)
                    <span class="text-heading mb-0">{{ $settings->currency_symbol }}{{ $order->discount }}</span>
                  @else
                    <span class="text-heading mb-0">{{ $settings->currency_symbol }}0</span>
                  @endif
                @endif
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="w-px-100">Envío:</span>
                <span class="text-heading">{{ $settings->currency_symbol }}{{ $order->shipping }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <h6 class="w-px-100 mb-0">Total:</h6>
                <h6 class="mb-0">{{ $settings->currency_symbol }}{{ $order->total }}</h6>
              </div>
            </div>
          </div>
      </div>
    </div>
<!-- Order Status Changes Table -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title m-0">Actualizaciones de la venta</h5>
  </div>
  <div class="card-body">
    <ul class="timeline pb-0 mb-0">
      @if($order->statusChanges == null || $order->statusChanges->isEmpty())
        <li class="timeline-item timeline-item-transparent border-primary">
          <span class="timeline-point-wrapper"><span class="timeline-point timeline-point-primary"></span></span>
          <div class="timeline-event">
            <div class="timeline-header">
              <h6 class="mb-0">Pedido creado (ID: #{{ $order->id }})</h6>
              <span class="text-muted">{{ $order->created_at->translatedFormat('l H:i A') }}</span>
            </div>
          </div>
        </li>
      @else
        @foreach($order->statusChanges->reverse() as $change)
          @php
            $oldBadgeClass = '';
            $newBadgeClass = '';
            switch ($change->old_status) {
              case 'pending':
                $oldBadgeClass = 'bg-warning';
                break;
              case 'paid':
              case 'shipped':
              case 'delivered':
                $oldBadgeClass = 'bg-success';
                break;
              case 'failed':
                $oldBadgeClass = 'bg-danger';
                break;
              default:
                $oldBadgeClass = 'bg-secondary';
                break;
            }
            switch ($change->new_status) {
              case 'pending':
                $newBadgeClass = 'bg-warning';
                break;
              case 'paid':
              case 'shipped':
                $newBadgeClass = 'bg-success';
                break;
              case 'delivered':
                $newBadgeClass = 'bg-info';
                break;
              case 'failed':
                $newBadgeClass = 'bg-danger';
                break;
              default:
                $newBadgeClass = 'bg-secondary';
                break;
            }
            $timelineClass = $newBadgeClass;
          @endphp
          <li class="timeline-item timeline-item-transparent border-{{ str_replace('bg-', '', $timelineClass) }}">
            <span class="timeline-point-wrapper">
              <span class="timeline-point {{ $timelineClass }}">
              </span>
            </span>
            <div class="timeline-event">
              <div class="timeline-header">
                <h6 class="mb-0">Estado de {{ $changeTypeTranslations[$change->change_type] ?? ucfirst($change->change_type) }} (Pedido: #{{ $change->order_id }})</h6>
                <span class="text-muted">{{ Carbon::parse($change->created_at)->locale('es')->translatedFormat('l H:i A') }}</span>
              </div>
              <p class="mt-2">
                @if($change->change_type === 'payment')
                  <span class="badge {{ $oldBadgeClass }}">{{ $paymentStatusTranslations[$change->old_status] ?? $change->old_status }}</span>
                  <i class="bx bx-right-arrow-alt mx-2"></i>
                  <span class="badge {{ $newBadgeClass }}">{{ $paymentStatusTranslations[$change->new_status] ?? $change->new_status }}</span>
                @elseif($change->change_type === 'shipping')
                  <span class="badge {{ $oldBadgeClass }}">{{ $shippingStatusTranslations[$change->old_status] ?? $change->old_status }}</span>
                  <i class="bx bx-right-arrow-alt mx-2"></i>
                  <span class="badge {{ $newBadgeClass }}">{{ $shippingStatusTranslations[$change->new_status] ?? $change->new_status }}</span>
                @else
                  <span class="badge {{ $oldBadgeClass }}">{{ $change->old_status }}</span>
                  <i class="bx bx-right-arrow-alt mx-2"></i>
                  <span class="badge {{ $newBadgeClass }}">{{ $change->new_status }}</span>
                @endif
                <br>
                <small class="text-muted">por {{ optional($change->user)->name ?? 'Usuario eliminado' }}</small>
              </p>
            </div>
          </li>
        @endforeach
      @endif
    </ul>
  </div>
</div>


  </div>

  <div class="col-12 col-lg-4">

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title m-0">Actualizar Estado del Pedido</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="payment_status" class="form-label">Estado del Pago:</label>
            <select name="payment_status" id="payment_status" class="form-select">
              <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pendiente</option>
              <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Pagado</option>
              <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Fallido</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="shipping_status" class="form-label">Estado del Envío:</label>
            <select name="shipping_status" id="shipping_status" class="form-select">
              <option value="pending" {{ $order->shipping_status === 'pending' ? 'selected' : '' }}>Pendiente</option>
              <option value="shipped" {{ $order->shipping_status === 'shipped' ? 'selected' : '' }}>Enviado</option>
              <option value="delivered" {{ $order->shipping_status === 'delivered' ? 'selected' : '' }}>Entregado</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Actualizar Estado del Pedido</button>
        </form>
      </div>
    </div>


    <div class="card mb-4">
      <div class="card-header">
        <h6 class="card-title m-0">Datos del Cliente</h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-start align-items-center mb-3">
          <div class="d-flex flex-column">
            <a href="{{ url('app/user/view/account') }}" class="text-body text-nowrap">
              <h5 class="mb-0">{{ ucwords($order->client->name) }} {{ ucwords($order->client->lastname) }}</h5>
            </a>
            <small class="text-muted">ID: #{{ $order->client->id }}</small>
            <small class="text-muted">Registrado el: {{ $order->client->created_at->format('d/m/Y') }}</small>
          </div>
        </div>

        <div class="mb-3">
          <h6 class="card-title mt-4">Información General</h6>
          <p class="mb-1"><strong>Tipo de Cliente:</strong> {{ $order->client->type === 'company' ? 'Empresa' : 'Persona' }}</p>
          @if($order->client->type === 'company')
            <p class="mb-1"><strong>Razón Social:</strong> {{ ucwords($order->client->company_name) }}</p>
          @endif
          <p class="mb-1"><strong>{{ $order->client->type === 'company' ? 'RUT' : 'CI' }}:</strong> {{ $order->client->type === 'company' ? $order->client->rut : $order->client->ci }}</p>
        </div>

        <div class="mb-3">
          <h6 class="card-title mt-4">Información de Contacto</h6>
          <p class="mb-1"><strong>Email:</strong> {{ $order->client->email }}</p>
          <p class="mb-1"><strong>Teléfono:</strong> {{ $order->client->phone }}</p>
          <p class="mb-1"><strong>Dirección:</strong> {{ ucwords($order->client->address) }}, {{ ucwords($order->client->city) }}, {{ ucwords($order->client->state) }}, {{ $order->client->country }}</p>
        </div>

        <div class="mb-3">
          <h6 class="card-title mt-4">Historial de Pedidos</h6>
          <div class="d-flex align-items-center">
            <span class="avatar rounded-circle bg-label-success me-2 d-flex align-items-center justify-content-center">
              <i class="bx bx-cart-alt bx-sm lh-sm"></i>
            </span>
            <p class="mb-0">
              {{ $clientOrdersCount }} {{ $clientOrdersCount > 1 ? 'Pedidos' : 'Pedido' }}
            </p>
          </div>
        </div>
      </div>
    </div>



  </div>
</div>

<!-- Modals -->
@include('content/e-commerce/backoffice/orders/bill-order')

@endsection
