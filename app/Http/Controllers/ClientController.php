<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Repositories\ClientRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\CompanySettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Client;
use App\Models\PriceList;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * El repositorio de clientes.
     *
     * @var ClientRepository
     */
    protected ClientRepository $clientRepository;

    /**
     * Constructor para inyectar el repositorio.
     *
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Muestra la página de lista de clientes.
     *
     * @return View
     */
    public function index(): View
    {
      $companySettings = CompanySettings::first();
      $store = Auth::user()->store_id;
      return view('content.clients.clients', compact('companySettings', 'store'));
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     *
     * @param StoreClientRequest $request
     * @return RedirectResponse
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            $companySettings = CompanySettings::first();
            if ($companySettings->clients_has_store == 1) {
                $validatedData['store_id'] = Auth::user()->store_id;
            }

            $this->clientRepository->createClient($validatedData);
            return redirect()->route('clients.index')->with('success', 'Cliente creado correctamente.');
        } catch (\Throwable $th) {
            return redirect()->route('clients.index')->with('error', 'Error al crear el cliente.');
        }
    }

    /**
     * Muestra los detalles de un cliente específico.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $client = $this->clientRepository->getClientById($id);
        return view('content.clients.show', compact('client'));
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $client = $this->clientRepository->getClientById($id);

        $orders = $client->orders;

        return view('content.clients.edit', compact('client'));
    }

    /**
     * Actualiza un cliente existente en la base de datos.
     *
     * @param UpdateClientRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
      \Log::info('Datos recibidos en la actualización:', $request->all());

        try {
            $validatedData = $request->validated();
            $client = $this->clientRepository->updateClient($id, $validatedData);
            return response()->json(['success' => true, 'message' => 'Cliente actualizado correctamente.', 'client' => $client]);
        } catch (\Exception $e) {
            // Captura el error completo en los logs para obtener más detalles
            \Log::error('Error al actualizar cliente: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cliente: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Elimina un cliente específico de la base de datos.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->clientRepository->deleteClient($id);
        return redirect()->route('clients.index');
    }

    /**
     * Obtiene los datos para mostrar en la tabla de clientes.
     *
     * @return mixed
     */
    public function datatable(): mixed
    {
        return $this->clientRepository->getClientsForDatatable();
    }

    public function getProductsByPriceList($priceListId)
    {
        // Obtener los productos y el precio específico de la lista de precios desde la tabla price_list_products
        $products = Product::select('products.id', 'products.name', 'price_list_products.price')
            ->join('price_list_products', 'products.id', '=', 'price_list_products.product_id')
            ->where('price_list_products.price_list_id', $priceListId)
            ->get();
    
        return response()->json(['products' => $products]);
    }
    

    public function getClientPriceList($clientId)
    {
        // Buscar el cliente por ID
        $client = Client::with('priceLists')->find($clientId);

        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Verificar si el cliente tiene una lista de precios asociada
        $priceListId = null;
        if ($client->priceLists->isNotEmpty()) {
            $priceListId = $client->priceLists->first()->id; // Obtenemos el primer ID de lista de precios
        }

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'lastname' => $client->lastname,
                'type' => $client->type,
                'company_name' => $client->company_name,
                'price_list_id' => $priceListId
            ]
        ]);
    }

    public function getPriceLists($clientId)
{
    try {
        // Registrar en el log el ID del cliente que estamos intentando obtener
        Log::info("Intentando obtener lista de precios para el cliente con ID: {$clientId}");

        // Buscar el cliente por su ID, cargando también la relación con la lista de precios
        $client = Client::with('priceList')->findOrFail($clientId);
        Log::info("Cliente encontrado: ", ['client' => $client]);

        // Verificar si el cliente tiene una lista de precios asignada
        if ($client->priceList) {
            Log::info("Lista de precios encontrada: ", ['price_list' => $client->priceList]);

            return response()->json([
                'status' => 'success',
                'price_list' => [
                    'id' => $client->priceList->id,
                    'name' => $client->priceList->name,
                ]
            ], 200);
        } else {
            Log::warning("El cliente con ID: {$clientId} no tiene una lista de precios asignada.");

            return response()->json([
                'status' => 'error',
                'message' => 'El cliente no tiene una lista de precios asignada'
            ], 404);
        }
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error("Cliente con ID: {$clientId} no encontrado. Excepción: " . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Cliente no encontrado'
        ], 404);
    } catch (\Exception $e) {
        Log::error("Error inesperado al obtener la lista de precios para el cliente con ID: {$clientId}. Excepción: " . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Error al obtener la lista de precios: ' . $e->getMessage()
        ], 500);
    }
}


}
