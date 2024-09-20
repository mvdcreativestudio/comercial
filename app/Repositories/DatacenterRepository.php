<?php

namespace App\Repositories;

use App\Enums\Expense\ExpenseStatusEnum;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Expense;
use App\Models\ExpensePaymentMethod;
use App\Models\Order;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;


class DatacenterRepository
{
    /**
     * Obtiene el rango de fechas basado en el período seleccionado.
     *
     * @param string $period
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getDateRange(string $period, string $startDate = null, string $endDate = null): array
    {
        $today = Carbon::today();
        $start = $startDate ? Carbon::parse($startDate) : $today;
        $end = $endDate ? Carbon::parse($endDate) : $today;

        switch ($period) {
            case 'today':
                return [$today->startOfDay(), $today->endOfDay()];
            case 'week':
                return [$today->copy()->subDays(6)->startOfDay(), $today->endOfDay()];
            case 'month':
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();
                return [$start, $end];
            case 'year':
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();
                return [$start, $end];
            case 'always':
                $firstSale = Order::min('date') ?? PosOrder::min('date');
                $start = $firstSale ? Carbon::parse($firstSale)->startOfMonth() : Carbon::minValue();
                $end = $end ?? Carbon::maxValue();
                return [$start, $end];
            case 'custom':
                return [$start->startOfDay(), $end->endOfDay()];
            default:
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
        }
    }


    /**
     * Contar la cantidad de locales.
     *
     * @return int
     */
    public function countStores(): int
    {
        return Store::count();
    }

    /**
     * Contar la cantidad de clientes con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return int
     */
    public function countClients(string $startDate, string $endDate, int $storeId = null): int
    {
        $query = Client::whereBetween('created_at', [$startDate, $endDate]);

        // Obtener configuración de companySettings usando el provider
        $companySettings = App::make('companySettings');

        // Verificar si clients_has_store está habilitado
        if ($companySettings && $companySettings->clients_has_store == 1) {
            if (Gate::allows('view_all_datacenter')) {
                // Si el usuario tiene el permiso, puede ver datos de todas las tiendas
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }
            } else {
                // Si no tiene el permiso, solo puede ver datos de su tienda
                $query->where('store_id', Auth::user()->store_id);
            }
        } else {
            // Si clients_has_store no está habilitado, se filtra por store_id si está definido
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        }

        return $query->count();
    }


    /**
     * Contar la cantidad de productos con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return int
     */
    public function countProducts(string $startDate, string $endDate, int $storeId = null): int
    {
        $query = Product::whereBetween('created_at', [$startDate, $endDate]);
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        return $query->count();
    }

    /**
     * Contar la cantidad de categorías.
     *
     * @return int
     */
    public function countCategories(): int
    {
        return ProductCategory::count();
    }

    /**
     * Contar la cantidad de órdenes con diferentes estados de envío con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function countOrders(string $startDate, string $endDate, int $storeId = null): array
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $posOrderQuery = PosOrder::whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $posOrderQuery->whereHas('cashRegisterLog.cashRegister', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            });
        }

        return [
            'delivered' => (clone $orderQuery)->where('shipping_status', 'delivered')->count() + $posOrderQuery->count(),
            'shipped' => (clone $orderQuery)->where('shipping_status', 'shipped')->count(),
            'pending' => (clone $orderQuery)->where('shipping_status', 'pending')->count(),
            'cancelled' => (clone $orderQuery)->where('shipping_status', 'cancelled')->count(),
        ];
    }

    /**
     * Calcular los ingresos de E-Commerce con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function ecommerceIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        $query = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'ecommerce');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        $totalPaidOrders = $query->sum('total');

        return number_format($totalPaidOrders, 0, ',', '.');
    }

    /**
     * Calcular los ingresos físicos con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function physicalIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        // Orders origin 'physical'
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->where('origin', 'physical');

        $posOrderQuery = PosOrder::whereBetween('date', [$startDate, $endDate]);

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
            $posOrderQuery->whereHas('cashRegisterLog.cashRegister', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            });
        }

        $totalOrderPaid = $orderQuery->sum('total');
        $totalPosOrderPaid = $posOrderQuery->sum('total');

        $totalPaid = $totalOrderPaid + $totalPosOrderPaid;

        return number_format($totalPaid, 0, ',', '.');
    }

    /**
     * Calcular los ingresos totales con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function totalIncomes(string $startDate, string $endDate, int $storeId = null): string
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        $posOrderQuery = PosOrder::whereBetween('date', [$startDate, $endDate]);

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
            $posOrderQuery->whereHas('cashRegisterLog.cashRegister', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            });
        }

        $totalOrderPaid = $orderQuery->sum('total');
        $totalPosOrderPaid = $posOrderQuery->sum('total');

        $totalPaid = $totalOrderPaid + $totalPosOrderPaid;

        return number_format($totalPaid, 0, ',', '.');
    }

    /**
     * Calcular la media mensual de ventas históricas.
     *
     * @param int|null $storeId
     * @return string
     */
    public function averageMonthlySales(int $storeId = null): string
    {
        // Consulta para Order
        $orderQuery = Order::select(DB::raw('SUM(total) as total'), DB::raw('YEAR(date) as year'), DB::raw('MONTH(date) as month'))
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'));

        // Consulta para PosOrder
        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->select(DB::raw('SUM(pos_orders.total) as total'), DB::raw('YEAR(pos_orders.date) as year'), DB::raw('MONTH(pos_orders.date) as month'))
            ->groupBy(DB::raw('YEAR(pos_orders.date)'), DB::raw('MONTH(pos_orders.date)'));

        // Aplicar filtro por store_id si es proporcionado
        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }

        // Obtener ventas mensuales combinadas de ambas consultas
        $monthlySales = DB::table(DB::raw("({$orderQuery->toSql()} UNION ALL {$posOrderQuery->toSql()}) as combined_sales"))
            ->mergeBindings($orderQuery->getQuery())
            ->mergeBindings($posOrderQuery->getQuery())
            ->select(DB::raw('SUM(total) as total'), 'year', 'month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Calcular promedio mensual
        if ($monthlySales->isEmpty()) {
            return '0';
        }

        $totalSales = $monthlySales->sum('total');
        $countMonths = $monthlySales->count();
        $averageMonthlySales = $totalSales / $countMonths;

        return number_format($averageMonthlySales, 0, ',', '.');
    }

    /**
     * Calcular el ticket medio con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return string
     */
    public function averageTicket(string $startDate, string $endDate, int $storeId = null): string
    {
        $orderQuery = Order::select(DB::raw('total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->select(DB::raw('pos_orders.total'))
            ->whereBetween('pos_orders.date', [$startDate, $endDate]);

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }

        $totalPaidOrders = DB::table(DB::raw("({$orderQuery->toSql()} UNION ALL {$posOrderQuery->toSql()}) as combined_orders"))
            ->mergeBindings($orderQuery->getQuery())
            ->mergeBindings($posOrderQuery->getQuery())
            ->sum('total');

        $totalPaidOrdersCount = DB::table(DB::raw("({$orderQuery->toSql()} UNION ALL {$posOrderQuery->toSql()}) as combined_orders"))
            ->mergeBindings($orderQuery->getQuery())
            ->mergeBindings($posOrderQuery->getQuery())
            ->count();

        if ($totalPaidOrdersCount > 0) {
            return number_format($totalPaidOrders / $totalPaidOrdersCount, 0, ',', '.');
        } else {
            return 'N/A';
        }
    }



/**
 * Obtener datos de ingresos con filtro de fecha y local.
 *
 * Este método obtiene los datos de ingresos agrupados por año, mes, día o hora dependiendo del período seleccionado.
 *
 * @param string $startDate La fecha de inicio del rango a consultar.
 * @param string $endDate La fecha de fin del rango a consultar.
 * @param int|null $storeId El ID del local para filtrar los resultados. Si es null, se consideran todos los locales.
 * @param string $period El período de agrupación de los resultados ('today', 'week', 'month', 'year', 'always').
 * @return EloquentCollection La colección de resultados agrupados.
 */
public function getIncomeData(string $startDate, string $endDate, int $storeId = null, string $period = 'month'): EloquentCollection
{
    // Selección y agrupación dinámica de campos según el periodo
    switch ($period) {
        case 'today':
            $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)'), DB::raw('DAY(date)'), DB::raw('HOUR(time)')];
            $selectFields = ['total', 'year', 'month', 'day', 'hour'];
            break;
        case 'week':
        case 'month':
            $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)'), DB::raw('DAY(date)')];
            $selectFields = ['total', 'year', 'month', 'day'];
            break;
        case 'year':
        case 'always':
        default:
            $groupBy = [DB::raw('YEAR(date)'), DB::raw('MONTH(date)')];
            $selectFields = ['total', 'year', 'month'];
            break;
    }


    // Consulta de pedidos del módulo de e-commerce
    $orderQuery = Order::select(
        DB::raw('SUM(total) as total'),
        DB::raw('YEAR(date) as year'),
        DB::raw('MONTH(date) as month'),
        DB::raw('DAY(date) as day'),
        DB::raw('HOUR(time) as hour')
    )
    ->where('payment_status', 'paid')
    ->whereBetween('date', [$startDate, $endDate])
    ->groupBy($groupBy);

    // Consulta de pedidos del módulo de POS
    $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
        ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
        ->select(
            DB::raw('SUM(pos_orders.total) as total'),
            DB::raw('YEAR(pos_orders.date) as year'),
            DB::raw('MONTH(pos_orders.date) as month'),
            DB::raw('DAY(pos_orders.date) as day'),
            DB::raw('HOUR(pos_orders.hour) as hour')
        )
        ->whereBetween('pos_orders.date', [$startDate, $endDate])
        ->groupBy($groupBy);

    // Aplicar filtro por store_id si se proporciona
    if ($storeId) {
        $orderQuery->where('store_id', $storeId);
        $posOrderQuery->where('cash_registers.store_id', $storeId);
    }

    // Unir los resultados de ambas consultas
    $combinedResults = $orderQuery->unionAll($posOrderQuery)->get();

    // Agregar cualquier campo faltante al resultado final
    $filledResults = $this->fillMissingData($combinedResults, $startDate, $endDate, $selectFields);
        $combinedQuery = DB::table(DB::raw("({$orderQuerySql} UNION ALL {$posOrderQuerySql}) as combined_sales"))
            ->mergeBindings($orderQuery->getQuery())
            ->mergeBindings($posOrderQuery->getQuery())
            ->select(DB::raw('SUM(total) as total'), 'year', 'month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc');

    return new EloquentCollection($filledResults);
}


/**
 * Rellenar los campos faltantes en los resultados de ingresos.
 *
 * Este método recorre un rango de fechas y verifica si para cada fecha existe un registro en la colección de resultados.
 * Si no existe, rellena los datos faltantes con 0.
 *
 * @param EloquentCollection $results La colección original de resultados.
 * @param string $startDate La fecha de inicio del rango a consultar.
 * @param string $endDate La fecha de fin del rango a consultar.
 * @param array $selectFields Los campos seleccionados para el período actual.
 * @return EloquentCollection La colección de resultados con los campos faltantes rellenados.
 */
private function fillMissingData(EloquentCollection $results, string $startDate, string $endDate, array $selectFields): EloquentCollection
{
    $filledResults = collect();

    // Crear un rango de fechas basado en el inicio y el final
    $period = Carbon::parse($startDate)->daysUntil($endDate);

    foreach ($period as $date) {
        // Busca si existe un registro que coincida con el grupo seleccionado (mes, día, hora, etc.)
        $matchingResult = $results->first(function ($item) use ($date, $selectFields) {
            foreach ($selectFields as $field) {
                // Compara las propiedades según el campo correspondiente
                switch ($field) {
                    case 'year':
                        if ($item->year != $date->year) return false;
                        break;
                    case 'month':
                        if ($item->month != $date->month) return false;
                        break;
                    case 'day':
                        if ($item->day != $date->day) return false;
                        break;
                    case 'hour':
                        if ($item->hour != $date->hour) return false;
                        break;
                }
            }
            return true;
        });

        // Si no se encuentra ningún resultado, se rellena con 0
        $filledResults->push([
            'year' => $date->year,
            'month' => $date->month,
            'day' => in_array('day', $selectFields) ? $date->day : null,
            'hour' => in_array('hour', $selectFields) ? $date->hour : null,
            'total' => $matchingResult ? $matchingResult->total : 0
        ]);
    }

    return new EloquentCollection($filledResults);
}







    /**
     * Obtener ventas por local en porcentaje para gráfica de torta.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getSalesByStoreData(int $storeId = null): array
    {
        $stores = Store::all();

        $orderQuery = Order::where('payment_status', 'paid');
        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }
        $totalPaidOrders = $orderQuery->sum('total');

        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->selectRaw('SUM(pos_orders.total) as total');
        if ($storeId) {
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }
        $totalPaidPosOrders = $posOrderQuery->first()->total;

        $totalPaidOrdersCombined = $totalPaidOrders + $totalPaidPosOrders;

        $data = [];

        foreach ($stores as $store) {
            $storeOrdersQuery = Order::where('store_id', $store->id)->where('payment_status', 'paid');
            if ($storeId) {
                $storeOrdersQuery->where('store_id', $storeId);
            }
            $storeOrders = $storeOrdersQuery->sum('total');

            $storePosOrdersQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
                ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
                ->where('cash_registers.store_id', $store->id)
                ->selectRaw('SUM(pos_orders.total) as total');
            if ($storeId) {
                $storePosOrdersQuery->where('cash_registers.store_id', $storeId);
            }
            $storePosOrders = $storePosOrdersQuery->first()->total;

            $storeTotalOrders = $storeOrders + $storePosOrders;

            if ($totalPaidOrdersCombined > 0) {
                $percent = ($storeTotalOrders / $totalPaidOrdersCombined) * 100;
            } else {
                $percent = 0;
            }

            $data[] = [
                'store' => $store->name,
                'percent' => number_format($percent, 2, ',', '.'),
            ];
        }

        return $data;
    }

    /**
     * Obtener porcentaje de ventas por local para tabla con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesPercentByStore(string $startDate, string $endDate): array
    {
        $totalPaidOrdersQuery = Order::whereBetween('date', [$startDate, $endDate])->where('payment_status', 'paid');
        $totalPaidOrders = $totalPaidOrdersQuery->sum('total');

        // Incluir ventas de PosOrder
        $totalPaidPosOrdersQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->whereBetween('pos_orders.date', [$startDate, $endDate])
            ->selectRaw('SUM(pos_orders.total) as total');
        $totalPaidPosOrders = $totalPaidPosOrdersQuery->first()->total;

        $totalPaidOrdersCombined = $totalPaidOrders + $totalPaidPosOrders;

        $stores = Store::with(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])->where('payment_status', 'paid');
        }])->get();

        $data = [];
        foreach ($stores as $store) {
            $storeTotal = $store->orders->sum('total');

            // Incluir ventas de PosOrder por tienda
            $storePosOrdersQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
                ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
                ->where('cash_registers.store_id', $store->id)
                ->whereBetween('pos_orders.date', [$startDate, $endDate])
                ->selectRaw('SUM(pos_orders.total) as total');
            $storePosOrders = $storePosOrdersQuery->first()->total;

            $storeTotalCombined = $storeTotal + $storePosOrders;
            $percent = $totalPaidOrdersCombined > 0 ? ($storeTotalCombined / $totalPaidOrdersCombined) * 100 : 0;
            $data[] = [
                'store' => $store->name,
                'percent' => round($percent, 2),
                'storeTotal' => number_format($storeTotalCombined, 0, ',', '.'),
            ];
        }

        usort($data, function ($a, $b) {
            return $b['storeTotal'] <=> $a['storeTotal'];
        });

        return $data;
    }

    /**
     * Obtener porcentaje de ventas por producto para tabla con filtro de fecha y local.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getSalesPercentByProduct(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Order::whereBetween('date', [$startDate, $endDate])->where('payment_status', 'paid');
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        $orders = $query->get();

        // Incluir ventas de PosOrder
        $posQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->whereBetween('pos_orders.date', [$startDate, $endDate]);
        if ($storeId) {
            $posQuery->where('cash_registers.store_id', $storeId);
        }
        $posOrders = $posQuery->get();

        $productSales = [];

        foreach ([$orders, $posOrders] as $orderCollection) {
            foreach ($orderCollection as $order) {
                $products = json_decode($order->products, true);

                if (is_array($products) && count($products) > 0) {
                    foreach ($products as $product) {
                        if (is_array($product) && isset($product['name'], $product['price'], $product['quantity'])) {
                            if (!isset($productSales[$product['name']])) {
                                $productSales[$product['name']] = [
                                    'total' => 0,
                                    'count' => 0,
                                ];
                            }
                            $productSales[$product['name']]['total'] += $product['price'] * $product['quantity'];
                            $productSales[$product['name']]['count'] += $product['quantity'];
                        }
                    }
                }
            }
        }

        $totalSales = array_sum(array_map(function ($product) {
            return $product['total'];
        }, $productSales));

        $data = [];
        foreach ($productSales as $name => $info) {
            $percent = $totalSales > 0 ? ($info['total'] / $totalSales) * 100 : 0;
            $data[] = [
                'product' => $name,
                'percent' => round($percent, 2),
                'productTotal' => $info['total'],
            ];
        }

        usort($data, function ($a, $b) {
            return $b['productTotal'] <=> $a['productTotal'];
        });

        return $data;
    }

    /**
     * Obtener datos de uso de cupones con el total descontado y ordenarlos.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getCouponsUsage(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Coupon::with(['orders' => function ($query) use ($startDate, $endDate, $storeId) {
            $query->whereBetween('date', [$startDate, $endDate]);
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        }]);
        $coupons = $query->get();

        $data = [];

        foreach ($coupons as $coupon) {
            $totalDiscount = $coupon->orders->sum('coupon_amount');
            $data[] = [
                'code' => $coupon->code,
                'total_discount' => $totalDiscount,
                'uses' => $coupon->orders->count(),
            ];
        }

        usort($data, function ($a, $b) {
            return $b['total_discount'] <=> $a['total_discount'];
        });

        return $data;
    }

    /**
     * Obtener el promedio de pedidos por hora para gráfica.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getAverageOrdersByHour(string $startDate = null, string $endDate = null, int $storeId = null): array
    {
        // Si hay un storeId definido, filtrar solo por ese storeId
        $stores = $storeId ? Store::where('id', $storeId)->get() : Store::all();
        $result = [];

        foreach ($stores as $store) {
            $orderQuery = Order::select(DB::raw('HOUR(time) as hour'), DB::raw('COUNT(*) as count'))
                ->where('payment_status', 'paid')
                ->where('store_id', $store->id)
                ->groupBy(DB::raw('HOUR(time)'));

            $posOrderQuery = PosOrder::select(DB::raw('HOUR(hour) as hour'), DB::raw('COUNT(*) as count'))
                ->whereHas('cashRegisterLog.cashRegister', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })
                ->groupBy(DB::raw('HOUR(hour)'));

            if ($startDate && $endDate) {
                $orderQuery->whereBetween('date', [$startDate, $endDate]);
                $posOrderQuery->whereBetween('date', [$startDate, $endDate]);
            }

            $orders = $orderQuery->get();
            $posOrders = $posOrderQuery->get();

            $hourlyData = array_fill(0, 24, 0);

            foreach ($orders as $order) {
                $hourlyData[$order->hour] += $order->count;
            }

            foreach ($posOrders as $posOrder) {
                $hourlyData[$posOrder->hour] += $posOrder->count;
            }

            $result[] = [
                'store' => $store->name,
                'data' => $hourlyData,
            ];
        }

        return $result;
    }

    /**
     * Obtiene los datos de ventas por categoría para tabla comparativa.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getSalesPercentByCategory(string $startDate, string $endDate, int $storeId = null): array
    {
        // Consulta a la tabla Order
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        // Consulta a la tabla PosOrder
        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->whereBetween('pos_orders.date', [$startDate, $endDate]);

        if ($storeId) {
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }

        $posOrders = $posOrderQuery->get(['pos_orders.products']);

        $categorySales = [];

        // Procesar pedidos de Order
        foreach ($orders as $order) {
            $products = json_decode($order->products, true);

            foreach ($products as $product) {
                if (!isset($product['category_id']) || !$product['category_id']) {
                    continue;
                }

                if (!isset($categorySales[$product['category_id']])) {
                    $categorySales[$product['category_id']] = [
                        'total' => 0,
                        'count' => 0,
                        'category_name' => ProductCategory::find($product['category_id'])->name ?? 'Sin categoría',
                    ];
                }

                $categorySales[$product['category_id']]['total'] += $product['price'] * $product['quantity'];
                $categorySales[$product['category_id']]['count'] += $product['quantity'];
            }
        }

        // Procesar pedidos de PosOrder
        foreach ($posOrders as $posOrder) {
            $products = json_decode($posOrder->products, true);

            foreach ($products as $product) {
                if (!isset($product['category_id']) || !$product['category_id']) {
                    continue;
                }

                if (!isset($categorySales[$product['category_id']])) {
                    $categorySales[$product['category_id']] = [
                        'total' => 0,
                        'count' => 0,
                        'category_name' => ProductCategory::find($product['category_id'])->name ?? 'Sin categoría',
                    ];
                }

                $categorySales[$product['category_id']]['total'] += $product['price'] * $product['quantity'];
                $categorySales[$product['category_id']]['count'] += $product['quantity'];
            }
        }

        $totalSales = array_sum(array_map(function ($category) {
            return $category['total'];
        }, $categorySales));

        $data = [];
        foreach ($categorySales as $category) {
            $percent = $totalSales > 0 ? ($category['total'] / $totalSales) * 100 : 0;
            $data[] = [
                'category' => $category['category_name'],
                'percent' => round($percent, 2),
                'categoryTotal' => $category['total'],
            ];
        }

        usort($data, function ($a, $b) {
            return $b['categoryTotal'] <=> $a['categoryTotal'];
        });

        return $data;
    }

    /**
     * Obtener datos de métodos de pago para gráfica de torta.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getPaymentMethodsData(string $startDate, string $endDate, int $storeId = null): array
    {
        $orderQuery = Order::whereBetween('date', [$startDate, $endDate])
            ->where('payment_status', 'paid');

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
        }

        $orders = $orderQuery->get();

        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->whereBetween('pos_orders.date', [$startDate, $endDate]);

        if ($storeId) {
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }

        $posOrders = $posOrderQuery->get(['pos_orders.cash_sales', 'pos_orders.pos_sales']);

        $paymentMethods = [
            'MercadoPago' => 0,
            'Efectivo' => 0,
            'Otro' => 0,
        ];

        foreach ($orders as $order) {
            $method = $order->payment_method;
            if ($method === 'mercadopago') {
                $paymentMethods['MercadoPago'] += $order->total;
            } elseif ($method === 'efectivo') {
                $paymentMethods['Efectivo'] += $order->total;
            }
        }

        foreach ($posOrders as $posOrder) {
            $paymentMethods['Efectivo'] += $posOrder->cash_sales;
            $paymentMethods['Otro'] += $posOrder->pos_sales;
        }

        $total = array_sum($paymentMethods);

        foreach ($paymentMethods as $method => $amount) {
            $paymentMethods[$method] = [
                'amount' => $amount,
                'percent' => $total > 0 ? ($amount / $total) * 100 : 0,
            ];
        }
        return $paymentMethods;
    }

    /**
     * Obtener datos de gastos para las cards de gastos.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */

    public function getExpensesData(string $startDate, string $endDate, int $storeId = null): array
    {
        $query = Expense::whereBetween('created_at', [$startDate, $endDate]);
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $expenses = $query->get();

        $totalExpenses = $expenses->sum('amount');
        $paidExpenses = $expenses->where('status', ExpenseStatusEnum::PAID)->count();
        $partialExpenses = $expenses->where('status', ExpenseStatusEnum::PARTIAL)->count();
        $unpaidExpenses = $expenses->where('status', ExpenseStatusEnum::UNPAID)->count();
        $data = [
            'total' => number_format($totalExpenses, 0, ',', '.'),
            'count' => $expenses->count(),
            'paid' => $paidExpenses,
            'partial' => $partialExpenses,
            'unpaid' => $unpaidExpenses,

        ];

        return $data;
    }
    /**
     * Calcular la media mensual de gastos históricos.
     *
     * @param int|null $storeId
     * @return string
     */
    public function averageMonthlyExpenses(int $storeId = null): string
    {
        // Consulta para Expense
        $expenseQuery = Expense::select(DB::raw('SUM(amount) as total'), DB::raw('YEAR(due_date) as year'), DB::raw('MONTH(due_date) as month'))
            ->where('status', ExpenseStatusEnum::PAID)
            ->groupBy(DB::raw('YEAR(due_date)'), DB::raw('MONTH(due_date)'));

        // Consulta para ExpensePaymentMethod
        $expensePaymentMethodQuery = ExpensePaymentMethod::join('expenses', 'expense_payment_methods.expense_id', '=', 'expenses.id')
            ->select(DB::raw('SUM(expense_payment_methods.amount_paid) as total'), DB::raw('YEAR(expense_payment_methods.payment_date) as year'), DB::raw('MONTH(expense_payment_methods.payment_date) as month'))
            ->where('expenses.status', ExpenseStatusEnum::PARTIAL)
            ->groupBy(DB::raw('YEAR(expense_payment_methods.payment_date)'), DB::raw('MONTH(expense_payment_methods.payment_date)'));

        // Aplicar filtro por store_id si es proporcionado
        if ($storeId) {
            $expenseQuery->where('store_id', $storeId);
            $expensePaymentMethodQuery->where('expenses.store_id', $storeId);
        }

        // Obtener gastos mensuales combinados de ambas consultas
        $monthlyExpenses = DB::table(DB::raw("({$expenseQuery->toSql()} UNION ALL {$expensePaymentMethodQuery->toSql()}) as combined_expenses"))
            ->mergeBindings($expenseQuery->getQuery())
            ->mergeBindings($expensePaymentMethodQuery->getQuery())
            ->select(DB::raw('SUM(total) as total'), 'year', 'month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Calcular promedio mensual
        if ($monthlyExpenses->isEmpty()) {
            return '0';
        }

        $totalExpenses = $monthlyExpenses->sum('total');
        $countMonths = $monthlyExpenses->count();
        $averageMonthlyExpenses = $totalExpenses / $countMonths;

        return number_format($averageMonthlyExpenses, 0, ',', '.');
    }

    public function getMonthlyExpensesData(int $storeId = null): EloquentCollection
    {
        $expenseQuery = Expense::select(DB::raw('SUM(amount) as total'), DB::raw('MONTH(due_date) as month'), DB::raw('YEAR(due_date) as year'))
            ->where('status', ExpenseStatusEnum::PAID)
            ->groupBy(DB::raw('YEAR(due_date)'), DB::raw('MONTH(due_date)'));

        $expensePaymentMethodQuery = ExpensePaymentMethod::join('expenses', 'expense_payment_methods.expense_id', '=', 'expenses.id')
            ->select(DB::raw('SUM(expense_payment_methods.amount_paid) as total'), DB::raw('MONTH(expense_payment_methods.payment_date) as month'), DB::raw('YEAR(expense_payment_methods.payment_date) as year'))
            ->where('expenses.status', ExpenseStatusEnum::PARTIAL)
            ->groupBy(DB::raw('YEAR(expense_payment_methods.payment_date)'), DB::raw('MONTH(expense_payment_methods.payment_date)'));

        if ($storeId) {
            $expenseQuery->where('store_id', $storeId);
            $expensePaymentMethodQuery->where('expenses.store_id', $storeId);
        }

        $expenseQuerySql = $expenseQuery->toSql();
        $expensePaymentMethodQuerySql = $expensePaymentMethodQuery->toSql();

        $combinedQuery = DB::table(DB::raw("({$expenseQuerySql} UNION ALL {$expensePaymentMethodQuerySql}) as combined_expenses"))
            ->mergeBindings($expenseQuery->getQuery())
            ->mergeBindings($expensePaymentMethodQuery->getQuery())
            ->select(DB::raw('SUM(total) as total'), 'year', 'month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc');

        $monthlyExpenses = $combinedQuery->get();

        return new EloquentCollection($monthlyExpenses);
    }
    // proveedores que más dinero se gastó
    public function getSuppliersExpensesData(string $startDate, string $endDate, int $storeId = null): array
    {
        // Construir la consulta base
        $query = Expense::select('supplier_id', DB::raw('SUM(amount) as total_spent'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('supplier_id')
            ->orderBy('total_spent', 'desc');

        // Aplicar filtro por store_id si es proporcionado
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Ejecutar la consulta y obtener los resultados
        $expensesData = $query->get();

        // Crear el arreglo de resultados
        $data = [];
        foreach ($expensesData as $expense) {
            $supplier = $expense->supplier; // Obtener el proveedor relacionado
            $data[] = [
                'supplier' => $supplier->name, // Aquí asumo que deseas el nombre del proveedor
                'total' => $expense->total_spent,
            ];
        }
        // dd($data);

        return $data;
    }
}
