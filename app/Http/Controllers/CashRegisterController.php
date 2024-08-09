<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCashRegisterRequest;
use App\Http\Requests\UpdateCashRegisterRequest;
use Illuminate\View\View;
use App\Repositories\CashRegisterRepository;
use App\Repositories\CashRegisterLogRepository;
use Illuminate\Support\Facades\Session;



class CashRegisterController extends Controller
{
    protected $cashRegisterRepository;
    protected $cashRegisterLogRepository;

    public function __construct(CashRegisterRepository $cashRegisterRepository, CashRegisterLogRepository $cashRegisterLogRepository)
    {
        $this->cashRegisterRepository = $cashRegisterRepository;
        $this->cashRegisterLogRepository = $cashRegisterLogRepository;
    }

    /**
     * Muestra una lista de todas las cajas registradoras.
     *
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $openCashRegisterId = $this->cashRegisterLogRepository->hasOpenLogForUser($userId);

        if ($openCashRegisterId) {
            Session::put('open_cash_register_id', $openCashRegisterId);
            return redirect()->route('pdv.front');
        } else {
            Session::forget('open_cash_register_id'); 
            $cajas = $this->cashRegisterRepository->getCashRegistersForDatatable($userId);
            return view('points-of-sales.index', compact('cajas', 'userId'));
        }
    }


    /**
     * Agrega una caja registradora a la base de datos.
     *
     * @param StoreCashRegisterRequest $request
     * @return JsonResponse
     */
    public function store(StoreCashRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $cashRegister = $this->cashRegisterRepository->createCashRegister($validatedData);
        return response()->json($cashRegister, 201);
    }

    /**
     * Devuelve una caja registradora dado un id.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $cashRegister = $this->cashRegisterRepository->getCashRegisterById($id);

        if ($cashRegister) {
            return response()->json($cashRegister);
        } else {
            return response()->json(['message' => 'Cash register not found.'], 404);
        }
    }

    /**
     * Actualiza una caja registradora ya creada.
     *
     * @param UpdateCashRegisterRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateCashRegisterRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $updated = $this->cashRegisterRepository->updateCashRegister($id, $validatedData);

        if ($updated) {
            return response()->json(['message' => 'Caja registradora actualizada correctamente.']);
        } else {
            return response()->json(['message' => 'Ha ocurrido un error al intentar actualizar la caja registradora.'], 404);
        }
    }

    /**
     * Borra una caja registradora dado un id.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        $deleted = $this->cashRegisterRepository->deleteCashRegister($id);

        if ($deleted) {
            return response()->json(['message' => 'Caja registradora borrada exitosamente.']);
        } else {
            return response()->json(['message' => 'No se pudo encontrar la caja registradora que se deseó borrar.'], 404);
        }
    }

    /**
     * Devuelve la(s) tienda(s) a las cuales le puede abrir una caha registradora.
     */
    public function storesForCashRegister()
    {
        $stores = $this->cashRegisterRepository->storesForCashRegister();
        return response()->json($stores, 201);
    }
}
