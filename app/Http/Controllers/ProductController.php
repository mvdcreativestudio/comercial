<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlavorRequest;
use App\Repositories\ProductRepository;
use App\Models\ProductCategory;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SwitchProductStatusRequest;
use App\Http\Requests\StoreMultipleFlavorsRequest;
use App\Http\Requests\UpdateFlavorRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\ExportService;
use App\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Auth;




class ProductController extends Controller
{
  /**
   * El repositorio para las operaciones de productos.
   *
   * @var ProductRepository
  */
  protected $productRepo;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param  ProductRepository  $productRepo
  */
  public function __construct(ProductRepository $productRepo)
  {
    $this->middleware(['check_permission:access_products', 'user_has_store'])->only(
        [
            'index',
            'show',
            'create',
            'store',
            'edit',
            'update',
            'destroy',
            'switchStatus',
            'flavors',
            'storeFlavor',
            'storeMultipleFlavors',
            'editFlavor',
            'updateFlavor',
            'destroyFlavor',
            'switchFlavorStatus'
        ]
    );

    $this->productRepo = $productRepo;
  }

  /**
   * Muestra una lista de todos los productos.
   *
   * @return View
  */
  public function index(): View
  {
    if (Auth::user()->can('access_global_products')) {
      $stores = Store::select('id', 'name')->get();
      $categories = ProductCategory::select('id', 'name')->get();
    } else {
      $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
      $categories = ProductCategory::select('id', 'name')->where('store_id', Auth::user()->store_id)->get();
    }
    return view('content.e-commerce.backoffice.products.products', compact('stores', 'categories'));
  }

  /**
   * Muestra un producto específico.
   *
   * @param int $id
   * @return View
   */
  public function show(int $id): View
  {
    $product = $this->productRepo->show($id);
    return view('content.e-commerce.backoffice.products.show-product', $product);
  }

    /**
   * Muestra una lista de todos los productos para Stock.
   *
   * @return View
   */
  public function stock(): View
  {
      if (Auth::user()->can('access_global_products')) {
          $stores = Store::select('id', 'name')->get();
      } else {
          $stores = Store::select('id', 'name')->where('id', Auth::user()->store_id)->get();
      }

      return view('content.e-commerce.backoffice.products.stock', compact('stores'));
  }


  /**
   * Muestra el formulario para crear un nuevo producto.
   *
   * @return View
  */
  public function create(): View
  {
    $product = $this->productRepo->create();
    return view('content.e-commerce.backoffice.products.add-product', $product);
  }

  /**
   * Almacena un nuevo producto en la base de datos.
   *
   * @param StoreProductRequest $request
   * @return RedirectResponse
  */
  public function store(StoreProductRequest $request): RedirectResponse
  {
    $validated = $request->validated();

    $this->productRepo->createProduct($request);

    return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
  }

  /**
   * Obtiene los datos de los productos para DataTables.
   *
   * @return mixed
  */
  public function datatable(Request $request): mixed
  {
      // Pasa el request al repositorio
      return $this->productRepo->getProductsForDataTable($request);
  }

  /**
   * Muestra el formulario para editar un producto.
   *
   * @param  int  $id
   * @return View
  */
  public function edit(int $id): View
  {
    $product = $this->productRepo->edit($id);
    return view('content.e-commerce.backoffice.products.edit-product', $product) ;
  }

  /**
   * Actualiza un producto específico en la base de datos.
   *
   * @param UpdateProductRequest $request
   * @param int $id
   * @return RedirectResponse
  */
  public function update(UpdateProductRequest $request, $id)
  {
    $this->productRepo->update($id, $request);
    return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
  }

  /**
    * Cambia el estado de un producto.
    *
    * @param SwitchProductStatusRequest $request
    * @return JsonResponse
  */
  public function switchStatus(SwitchProductStatusRequest $request): JsonResponse
  {
    $this->productRepo->switchProductStatus($request->id);
    return response()->json(['success' => true, 'message' => 'Estado del producto actualizado correctamente.']);
  }

  /**
   * Elimina un producto de la base de datos.
   *
   * @param int $id
   * @return RedirectResponse
  */
  public function destroy(int $id): RedirectResponse
  {
    $this->productRepo->delete($id);
    return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
  }

  /**
   * Muestra una lista de todos los variaciones.
   *
   * @return View
  */
  public function flavors(): View
  {
    $flavors = $this->productRepo->flavors();
    return view('content.e-commerce.backoffice.products.flavors', $flavors);
  }

  /**
   * Obtiene los datos de los variaciones para DataTables.
   *
   * @return mixed
  */
  public function flavorsDatatable(): mixed
  {
    return $this->productRepo->flavorsDatatable();
  }

  /**
   * Almacena un sabor
   *
   * @param StoreFlavorRequest $request
   * @return RedirectResponse
  */
  public function storeFlavor(StoreFlavorRequest $request): RedirectResponse
  {
    $this->productRepo->storeFlavor($request);
    return redirect()->route('product-flavors')->with('success', 'Sabor creado correctamente.');
  }

  /**
   * Almacena múltiples variaciones
   *
   * @param StoreMultipleFlavorsRequest $request
   * @return JsonResponse
  */
  public function storeMultipleFlavors(StoreMultipleFlavorsRequest $request): JsonResponse
  {
    $this->productRepo->storeMultipleFlavors($request);
    return response()->json(['success' => true, 'message' => 'Variaciones múltiples creados correctamente.']);
  }

  /**
   * Muestra el formulario para editar un sabor.
   *
   * @param  int  $id
   * @return View
  */
  public function editFlavor(int $id): JsonResponse
  {
    $flavor = $this->productRepo->editFlavor($id);
    return response()->json($flavor);
  }

  /**
   * Actualiza un sabor específico en la base de datos.
   *
   * @param  UpdateFlavorRequest  $request A CAMBIAR JEJEJE
   * @param  int  $id
   * @return View
  */
  public function updateFlavor(UpdateFlavorRequest $request, int $id): JsonResponse
  {
      $flavor = $this->productRepo->updateFlavor($request, $id);
      return response()->json(['success' => true, 'message' => 'Sabor actualizado con éxito']);
  }

  /**
   * Elimina un sabor de la base de datos.
   *
   * @param  int  $id
   * @return JsonResponse
  */
  public function destroyFlavor(int $id): JsonResponse
  {
    $this->productRepo->destroyFlavor($id);
    return response()->json(['success' => true, 'message' => 'Sabor eliminado correctamente.']);
  }

  /**
   * Cambia el estado de un sabor.
   *
   * @param  int  $id
   * @return JsonResponse
  */
  public function switchFlavorStatus(int $id): JsonResponse
  {
    $this->productRepo->switchFlavorStatus($id);
    return response()->json(['success' => true, 'message' => 'Estado del sabor actualizado correctamente.']);
  }

  /**
   * Exporta los productos a un archivo de Excel.
   *
   * @param Request $request
   * @return mixed
   */
  public function exportToExcel(Request $request)
  {
      $filters = $request->all();  // Capturar todos los filtros
      $products = Product::filterData($filters)->get()->toArray();  // Asegúrate de convertir los datos a array

      return Excel::download(new GenericExport($products), 'productos_filtrados.xlsx');
  }

  /**
   * Importa productos desde un archivo de Excel.
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function import(Request $request)
  {
      $request->validate([
          'file' => 'required|mimes:xlsx'
      ]);

      // Log para ver si el archivo fue recibido correctamente
      if ($request->hasFile('file')) {
          \Log::info('Archivo recibido: ' . $request->file('file')->getClientOriginalName());
      } else {
          \Log::error('No se recibió ningún archivo.');
      }

      // Ahora intenta la importación
      try {
          Excel::import(new ProductsImport, $request->file('file'));
          \Log::info('Importación exitosa.');
      } catch (\Exception $e) {
          \Log::error('Error durante la importación: ' . $e->getMessage());
      }

      return redirect()->route('products.index')->with('success', 'Productos importados correctamente.');
  }

  /**
   * Muestra el formulario para editar productos en masa.
   *
   * @return View
   *
   */
  public function editBulk() {
    $products = Product::all();
    return view('products.editBulk', compact('products'));
  }

  /**
   * Actualiza los productos en masa.
   *
   * @param Request $request
   * @return RedirectResponse
   */
   public function updateBulk(Request $request) {
    $products = $request->input('products');
    foreach ($products as $productData) {
        $product = Product::find($productData['id']);
        if ($product) {
            $product->update($productData);
        }
    }
    return redirect()->route('products.editBulk')->with('success', 'Productos actualizados correctamente.');
  }

  /**
   * Muestra el formulario para agregar productos en masa.
   *
   * @return View
   */
  public function addBulk(): View
  {
    $stores = Store::all();
    return view('products.addBulk', compact('stores'));
  }

  /**
   * Almacena los productos en masa.
   *
   * @param Request $request
   * @return RedirectResponse
   */
  public function storeBulk(Request $request): RedirectResponse
  {
    \Log::info('Iniciando el proceso de almacenamiento en masa de productos.');

    try {
        \Log::info('Datos de la solicitud:', $request->all());

        $products = $request->input('products');
        \Log::info('Productos recibidos:', $products);

        foreach ($products as $index => $productData) {
            if (empty($productData['name'])) {
                \Log::info("Producto en índice $index omitido debido a falta de nombre.");
                continue;
            }

            // Siempre asignar la imagen por defecto
            $productData['image'] = '/assets/img/ecommerce-images/placeholder.png';

            $validatedData = $request->validate([
                "products.$index.name" => 'required|string|max:255',
                "products.$index.old_price" => 'nullable|numeric',
                "products.$index.price" => 'required|numeric',
                "products.$index.stock" => 'required|integer',
                "products.$index.safety_margin" => 'required|integer',
                "products.$index.store_id" => 'required|exists:stores,id',
            ]);

            // Agregar la imagen a los datos validados
            $validatedData["products"][$index]['image'] = $productData['image'];

            \Log::info("Datos validados para el producto en índice $index:", $validatedData);

            try {
                $product = new Product($validatedData["products"][$index]);
                $product->save();
                \Log::info("Producto guardado correctamente: ID {$product->id}");
            } catch (\Exception $e) {
                \Log::error("Error al guardar el producto en índice $index: " . $e->getMessage(), [
                    'productData' => $productData,
                    'exception' => $e,
                ]);
            }
        }

        return redirect()->route('products.addBulk')->with('success', 'Productos agregados correctamente.');
    } catch (\Exception $e) {
        \Log::error('Error en el proceso de almacenamiento en masa: ' . $e->getMessage(), [
            'exception' => $e,
        ]);
        return redirect()->route('products.addBulk')->with('error', 'Ocurrió un error al agregar los productos.');
    }
  }

}
