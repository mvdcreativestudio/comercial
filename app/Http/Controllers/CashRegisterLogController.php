<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterLogRequest;
use App\Http\Requests\UpdateCashRegisterLogRequest;
use App\Repositories\CashRegisterLogRepository;
use Illuminate\Support\Facades\Log;


class CashRegisterLogController extends Controller
{

    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterLogRepository $cashRegisterLogRepository)
    {
        Log::info('CashRegisterLogRepository inyectado correctamente.');
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pdv.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Agrega un log de caja registradora a la base de datos.
     * La función del método es abrir la caja registradora ese día. 
     *
     * @param StoreCashRegisterLogRequest $request
     * @param JsonResponse
     */
    public function store(StoreCashRegisterLogRequest $request)
    {

        $cashRegisterId = $request->input('cash_register_id');
    
        // Verificar si hay un log existente sin fecha de cierre
        if ($this->cashRegisterLogRepository->hasOpenLog()) {
            return response()->json(['message' => 'Ya existe una caja registradora abierta.'], 400);
        }

        $request['open_time'] = now();
        $request['cash_sales'] = 0;
        $request['pos_sales'] = 0;
        $validatedData = $request->validated();
        $cashRegisterLog = $this->cashRegisterLogRepository->createCashRegisterLog($validatedData);
        return response()->json($cashRegisterLog, 201);
    }

    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Actualiza un log de una caja registradora.
     *
     * @param UpdateCashRegisterLogRequest $request
     * @param string $id
     */
    public function update(UpdateCashRegisterLogRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterLogRepository->updateCashRegisterLog($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Cash register log updated successfully.']);
        } else {
            return response()->json(['message' => 'Cash register log not found or not updated.'], 404);
        }
    }

    /**
     * Borra un log de caja registradora dado un id.
     *
     * @param string $id
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterLogRepository->deleteCashRegisterLog($id);

        if ($deleted) {
            return response()->json(['message' => 'Log de caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar el log de la caja registradora que se deseó borrar.'], 404);
        }
    }


    /**
     * Cierre de caja.
     *
     * @param string $id
     */
    public function closeCashRegister(string $id)
    {
        $closed = $this->cashRegisterLogRepository->closeCashRegister($id);

        if ($closed) {
            return response()->json(['message' => 'Caja registradora cerrada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error intentando cerrar la caja registradora.'], 404);
        }
    }


    /**
     * Toma los productos de la tienda de la caja registradora.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByCashRegister(int $id)
    {
        $products = $this->cashRegisterLogRepository->getAllProductsForPOS($id);
        return response()->json(['products' => $products]);
    }

    /**
     * Toma los productos de la tienda de la caja registradora.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFlavorsForCashRegister()
    {
        try {
            $flavors = $this->cashRegisterLogRepository->getFlavors();
            Log::info('Productos obtenidos:', $flavors->toArray());
            return response()->json(['flavors' => $flavors]);
    
        } catch (\Exception $e) {
            Log::error('Error al obtener los productos:', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
    

    /**
     * Toma las categorías padres.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFathersCategories()
    {
        try {
            $categories = $this->cashRegisterLogRepository->getCategories();
            return response()->json(['categories' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
