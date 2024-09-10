<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\AccountingRepository;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
  /**
   * El repositorio de tiendas.
   *
   * @var StoreRepository
  */
  protected StoreRepository $storeRepository;

  /**
   * El repositorio de contabilidad.
   *
   * @var AccountingRepository
  */
  protected AccountingRepository $accountingRepository;

  /**
   * Constructor para inyectar el repositorio.
   *
   * @param StoreRepository $storeRepository
   * @param AccountingRepository $accountingRepository
  */
  public function __construct(StoreRepository $storeRepository, AccountingRepository $accountingRepository)
  {
    $this->storeRepository = $storeRepository;
    $this->accountingRepository = $accountingRepository;
  }

  /**
   * Muestra una lista de todas las tiendas.
   *
   * @return View
  */
  public function index(): View
  {
    $stores = $this->storeRepository->getAll();
    return view('stores.index', compact('stores'));
  }

  /**
   * Muestra el formulario para crear una nueva tienda.
   *
   * @return View
  */
  public function create(): View
  {
    return view('stores.create', ['googleMapsApiKey' => config('services.google.maps_api_key')]);
  }

  /**
   * Almacena una nueva tienda en la base de datos.
   *
   * @param StoreStoreRequest $request
   * @return RedirectResponse
  */
  public function store(StoreStoreRequest $request): RedirectResponse
  {
    $storeData = $request->validated();

    $store = $this->storeRepository->create(Arr::except($storeData, [
        'mercadoPagoPublicKey',
        'mercadoPagoAccessToken',
        'mercadoPagoSecretKey',
        'accepts_mercadopago',
        'pymo_user',
        'pymo_password',
        'pymo_branch_office'
    ]));

    if ($request->boolean('accepts_mercadopago')) {
        $store->mercadoPagoAccount()->create([
            'store_id' => $store->id,
            'public_key' => $request->input('mercadoPagoPublicKey'),
            'access_token' => $request->input('mercadoPagoAccessToken'),
            'secret_key' => $request->input('mercadoPagoSecretKey'),
        ]);
    }

    if ($request->boolean('invoices_enabled')) {
      $store->update([
          'pymo_user' => $request->input('pymo_user'),
          'pymo_password' => Crypt::encryptString($request->input('pymo_password')),
          'pymo_branch_office' => $request->input('pymo_branch_office'),
      ]);
  }

    return redirect()->route('stores.index')->with('success', 'Tienda creada con éxito.');
  }

  /**
   * Muestra una tienda específica.
   *
   * @param Store $store
   * @return View
  */
  public function show(Store $store): View
  {
    return view('stores.show', compact('store'));
  }

  /**
   * Muestra el formulario para editar una tienda existente.
   *
   * @param Store $store
   * @return View
  */
  public function edit(Store $store): View
  {
      $googleMapsApiKey = config('services.google.maps_api_key');

      $companyInfo = null;
      $logoUrl = null;

      if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password) {
          $companyInfo = $this->accountingRepository->getCompanyInfo($store->rut);
          $logoUrl = $this->accountingRepository->getCompanyLogo($store->rut);
      }

      return view('stores.edit', compact('store', 'googleMapsApiKey', 'companyInfo', 'logoUrl'));
  }


  /**
   * Actualiza una tienda específica en la base de datos.
   *
   * @param UpdateStoreRequest $request
   * @param Store $store
   * @return RedirectResponse
  */
  public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
  {
      $storeData = $request->validated();

      $this->storeRepository->update($store, Arr::except($storeData, [
          'mercadoPagoPublicKey',
          'mercadoPagoAccessToken',
          'mercadoPagoSecretKey',
          'accepts_mercadopago',
          'pymo_user',
          'pymo_password',
          'pymo_branch_office'
      ]));

      if ($request->boolean('accepts_mercadopago')) {
          $store->mercadoPagoAccount()->updateOrCreate(['store_id' => $store->id], [
              'public_key' => $request->input('mercadoPagoPublicKey'),
              'access_token' => $request->input('mercadoPagoAccessToken'),
              'secret_key' => $request->input('mercadoPagoSecretKey'),
          ]);
      } else {
          $store->mercadoPagoAccount()->delete();
      }

      // Actualizar solo si invoices_enabled es true
      if ($request->boolean('invoices_enabled')) {
        $updateData = [
            'pymo_user' => $request->input('pymo_user'),
            'pymo_branch_office' => $request->input('pymo_branch_office'),
        ];

        if ($request->filled('pymo_password') && $request->input('pymo_password') !== $store->pymo_password) {
            $updateData['pymo_password'] = Crypt::encryptString($request->input('pymo_password'));
        }

        $store->update($updateData);
    } else {
        $store->update([
            'pymo_user' => null,
            'pymo_password' => null,
            'pymo_branch_office' => null,
        ]);
    }



      return redirect()->route('stores.index')->with('success', 'Tienda actualizada con éxito.');
  }

  /**
   * Elimina la tienda.
   *
   * @param Store $store
   * @return RedirectResponse
  */
  public function destroy(Store $store): RedirectResponse
  {
    $this->storeRepository->delete($store);
    return redirect()->route('stores.index')->with('success', 'Tienda eliminada con éxito.');
  }

  /**
   * Cambia el estado de la tienda.
   *
   * @param Store $store
   * @return RedirectResponse
  */
  public function toggleStoreStatus(Store $store): RedirectResponse
  {
    $this->storeRepository->toggleStoreStatus($store);
    return redirect()->route('stores.index')->with('success', 'Estado de la tienda cambiado con éxito.');
  }


  /**
   * Cambia el abierto/cerrado de la tienda.
   *
   * @param $id
   * @return RedirectResponse
  */
  public function toggleStoreStatusClosed($storeId)
  {
      $success = $this->storeRepository->toggleStoreStatusClosed($storeId);

      if ($success) {
          $store = Store::findOrFail($storeId);
          return response()->json(['status' => 'success', 'closed' => $store->closed]);
      } else {
          return response()->json(['status' => 'error', 'message' => 'No se pudo cambiar el estado de la tienda.'], 500);
      }
  }



  /**
   * Muestra la página para administrar usuarios asociados a una tienda.
   *
   * @param Store $store
   * @return View
  */
  public function manageUsers(Store $store): View
  {
    $unassociatedUsers = $this->storeRepository->getUnassociatedUsers();
    $associatedUsers = $store->users;
    return view('stores.manage-users', compact('store', 'unassociatedUsers', 'associatedUsers'));
  }

  /**
   * Asocia un usuario a una tienda.
   *
   * @param Request $request
   * @param Store $store
   * @return RedirectResponse
  */
  public function associateUser(Request $request, Store $store): RedirectResponse
  {
    $this->storeRepository->associateUser($store, $request->get('user_id'));
    return redirect()->back()->with('success', 'Usuario asociado con éxito.');
  }

  /**
   * Desasocia un usuario de una tienda.
   *
   * @param Request $request
   * @param Store $store
   * @return RedirectResponse
  */
  public function disassociateUser(Request $request, Store $store): RedirectResponse
  {
    $this->storeRepository->disassociateUser($store, $request->get('user_id'));
    return redirect()->back()->with('success', 'Usuario desasociado con éxito.');
  }

  /**
   * Muestra la página para administrar los horarios de una tienda.
   *
   * @param Store $store
   * @return View
   */
  public function manageHours(Store $store): View
  {
    $storeHours = $store->storeHours->keyBy('day');
    return view('stores.manage-hours', compact('store', 'storeHours'));
  }

  /**
   * Guarda los horarios de una tienda.
   *
   * @param Store $store
   * @param Request $request
   * @return RedirectResponse
  */
  public function saveHours(Store $store, Request $request): RedirectResponse
  {
    $this->storeRepository->saveStoreHours($store, $request->get('hours', []));
    return redirect()->route('stores.index', ['store' => $store->id])->with('success', 'Horarios actualizados con éxito.');
  }

  /**
   * Cambia el estado de cierre de una tienda.
   *
   * @param Request $request
   * @param int $storeId
   * @return JsonResponse
  */
  public function closeStoreStatus(Request $request, int $storeId)
  {
    $store = Store::findOrFail($storeId);
    $store->closed = $request->input('closed');
    $store->save();

    return response()->json(['message' => 'Estado actualizado correctamente', 'newState' => $store->closed]);
  }

  /**
   * Obtiene el estado de todas las tiendas.
   *
   * @return JsonResponse
   */
  public function getAllStoreStatuses()
  {
    $storeStatuses = $this->storeRepository->getStoresWithStatus()->map(function ($store) {
        return [
            'id' => $store->id,
            'status' => $store->closed ? 'closed' : 'open'
        ];
    });

    return response()->json($storeStatuses);
  }

  /**
   * Cambia el estado de la facturación automática de la tienda.
   *
   * @param Store $store
   * @return RedirectResponse
  */
  public function toggleAutomaticBilling(Store $store): RedirectResponse
  {
    $this->storeRepository->toggleAutomaticBilling($store);
    return redirect()->route('stores.index')->with('success', 'Estado de facturación automática cambiado con éxito.');
  }
}
