<?php

namespace App\Repositories;

use App\Models\Store;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Order;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use App\Models\PosOrder;


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
                return [$today, $today->copy()->endOfDay()];
            case 'week':
                $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
                $weekEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);
                return [$weekStart, $weekEnd];
            case 'month':
                $monthStart = $today->copy()->startOfMonth();
                $monthEnd = $today->copy()->endOfMonth();
                return [$monthStart, $monthEnd];
            case 'year':
                $yearStart = $today->copy()->startOfYear();
                $yearEnd = $today->copy()->endOfYear();
                return [$yearStart, $yearEnd];
            case 'always':
                return [Carbon::minValue(), Carbon::maxValue()];
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
            'cancelled' => (clone $orderQuery)->where('shipping_status', 'cancelled')->count()
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
     * Obtener datos de ventas mensuales.
     *
     * @param int|null $storeId
     * @return EloquentCollection
     */
    public function getMonthlyIncomeData(int $storeId = null): EloquentCollection
    {
        $orderQuery = Order::select(DB::raw('SUM(total) as total'), DB::raw('MONTH(date) as month'), DB::raw('YEAR(date) as year'))
            ->where('payment_status', 'paid')
            ->groupBy(DB::raw('YEAR(date)'), DB::raw('MONTH(date)'));

        $posOrderQuery = PosOrder::join('cash_register_logs', 'pos_orders.cash_register_log_id', '=', 'cash_register_logs.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->select(DB::raw('SUM(pos_orders.total) as total'), DB::raw('MONTH(pos_orders.date) as month'), DB::raw('YEAR(pos_orders.date) as year'))
            ->groupBy(DB::raw('YEAR(pos_orders.date)'), DB::raw('MONTH(pos_orders.date)'));

        if ($storeId) {
            $orderQuery->where('store_id', $storeId);
            $posOrderQuery->where('cash_registers.store_id', $storeId);
        }

        $orderQuerySql = $orderQuery->toSql();
        $posOrderQuerySql = $posOrderQuery->toSql();

        $combinedQuery = DB::table(DB::raw("({$orderQuerySql} UNION ALL {$posOrderQuerySql}) as combined_sales"))
            ->mergeBindings($orderQuery->getQuery()) 
            ->mergeBindings($posOrderQuery->getQuery()) 
            ->select(DB::raw('SUM(total) as total'), 'year', 'month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc');

        $monthlySales = $combinedQuery->get();

        return new EloquentCollection($monthlySales);
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
                'percent' => number_format($percent, 2, ',', '.')
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
                                    'count' => 0
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
                'uses' => $coupon->orders->count()
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
    public function getAverageOrdersByHour(string $startDate = null, string $endDate = null): array
    {
        $stores = Store::all();
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
                'data' => $hourlyData
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
                        'category_name' => ProductCategory::find($product['category_id'])->name ?? 'Sin categoría'
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
                        'category_name' => ProductCategory::find($product['category_id'])->name ?? 'Sin categoría'
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
}
