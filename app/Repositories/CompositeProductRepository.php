<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\Models\CompositeProduct;
use App\Models\CompositeProductDetail;
use App\Models\Product;
use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompositeProductRepository
{
    /**
     * Obtiene todos los productos compuestos y estadísticas necesarias.
     *
     * @return array
     */
    public function getAllCompositeProducts(): array
    {
        // Verificar si el usuario tiene permiso para ver todos los productos compuestos de la tienda
        if (Auth::user()->can('view_all_composite-products')) {
            // Si tiene el permiso, obtenemos todos los productos compuestos
            $compositeProducts = CompositeProduct::all();
        } else {
            // Si no tiene el permiso, solo obtenemos los productos compuestos de su store_id
            $compositeProducts = CompositeProduct::where('store_id', Auth::user()->store_id)->get();
        }

        // Calcular las estadísticas basadas en los productos compuestos filtrados
        $totalProducts = $compositeProducts->count();
        $totalPrice = $compositeProducts->sum('price');
        $totalRecommendedPrice = $compositeProducts->sum('recommended_price');

        return compact('compositeProducts', 'totalProducts', 'totalPrice', 'totalRecommendedPrice');
    }

    /**
     * Muestra el formulario para crear un nuevo producto compuesto.
     *
     * @return array
     */
    public function create(): array
    {
        $stores = $this->getAllStores();
        $products = $this->getAllProducts();
        return compact('stores', 'products');
    }

    /**
     * Almacena un nuevo producto compuesto en la base de datos.
     *
     * @param  array  $data
     * @param  array  $productIds
     * @return CompositeProduct
     */
    public function store(array $data, array $productIds): CompositeProduct
    {
        DB::beginTransaction();

        try {
            $compositeProduct = new CompositeProduct();
            $compositeProduct->fill($data);
            // cambiar el precio recomendado sumando los precios de los productos individuales
            $compositeProduct->recommended_price = Product::whereIn('id', $productIds)->sum('price');
            $compositeProduct->save();

            // Añadir productos individuales al producto compuesto
            foreach ($productIds as $productId) {
                CompositeProductDetail::create([
                    'composite_product_id' => $compositeProduct->id,
                    'product_id' => $productId,
                ]);
            }

            DB::commit();
            return $compositeProduct;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene un producto compuesto específico por su ID.
     *
     * @param int $productId
     * @return array
     */
    public function getCompositeProductById(int $productId): array
    {
        $compositeProduct = CompositeProduct::findOrFail($productId)->load('details');
        $stores = $this->getAllStores();
        $products = $this->getAllProducts();
        $compositeProduct->product_ids = $compositeProduct->details->pluck('product_id')->toArray();
        return compact('compositeProduct', 'stores', 'products');
    }

    /**
     * Actualiza un producto compuesto específico en la base de datos.
     *
     * @param CompositeProduct $compositeProduct
     * @param array $data
     * @param array $productIds
     * @return CompositeProduct
     */
    public function update(CompositeProduct $compositeProduct, array $data): CompositeProduct
    {
        DB::beginTransaction();

        try {
            $productIds = $data['product_ids'];
            $compositeProduct->update($data);

            // Obtener los IDs de los detalles existentes
            $existingProductIds = CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->pluck('product_id')
                ->toArray();

            // Eliminar los detalles que no están en $productIds
            CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->whereNotIn('product_id', $productIds)
                ->delete();

            // Añadir los nuevos detalles que no están en los detalles existentes
            foreach ($productIds as $productId) {
                if (!in_array($productId, $existingProductIds)) {
                    CompositeProductDetail::create([
                        'composite_product_id' => $compositeProduct->id,
                        'product_id' => $productId,
                    ]);
                }
            }

            // Actualizar el precio recomendado
            $compositeProduct->recommended_price = CompositeProductDetail::where('composite_product_id', $compositeProduct->id)
                ->join('products', 'composite_product_details.product_id', '=', 'products.id')
                ->sum('products.price');
            $compositeProduct->save();

            DB::commit();
            return $compositeProduct;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un producto compuesto específico.
     *
     * @param int $productId
     * @return void
     */
    public function destroyCompositeProduct(int $productId): void
    {
        $compositeProduct = CompositeProduct::findOrFail($productId);
        $compositeProduct->delete();
    }

    /**
     * Eliminar varios productos compuestos.
     *
     * @param array $productIds
     * @return void
     */
    public function deleteMultipleCompositeProducts(array $productIds): void
    {
        CompositeProduct::whereIn('id', $productIds)->delete();
    }

    /**
     * Obtiene los productos compuestos para la DataTable.
     *
     * @return mixed
     */
    public function getCompositeProductsForDataTable(Request $request): mixed
    {
        $query = CompositeProduct::select([
            'composite_products.id',
            'composite_products.title',
            'composite_products.price',
            'composite_products.recommended_price',
            'composite_products.store_id',
            'composite_products.created_at',
            'stores.name as store_name',
        ])
            ->join('stores', 'composite_products.store_id', '=', 'stores.id')
            ->orderBy('composite_products.created_at', 'desc');

        // Verificar permisos del usuario
        if (!Auth::user()->can('view_all_composite-products')) {
            $query->where('composite_products.store_id', Auth::user()->store_id);
        }

         // Filtrar por rango de fechas
         if (Helpers::validateDate($request->input('start_date')) && Helpers::validateDate($request->input('end_date'))) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('composite_products.created_at', [$startDate, $endDate]);
        }

        $dataTable = DataTables::of($query)->make(true);

        return $dataTable;
    }

    /**
     * Obtiene todas las tiendas.
     *
     * @return mixed
     */
    public function getAllStores(): mixed
    {
        return Store::all();
    }

    /**
     * Obtiene todos los productos.
     *
     * @return mixed
     */
    public function getAllProducts(): mixed
    {
        return Product::whereNotNull('price')->get();
    }
}
