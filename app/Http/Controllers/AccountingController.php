<?php

namespace App\Http\Controllers;

use App\Repositories\AccountingRepository;
use App\Http\Requests\SaveRutRequest;
use App\Http\Requests\UploadLogoRequest;
use Yajra\DataTables\DataTables;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\EmitNoteRequest;
use Illuminate\Support\Facades\Log;
use App\Models\CFE;

class AccountingController extends Controller
{
    /**
     * Repositorio de contabilidad.
     *
     * @var AccountingRepository
    */
    protected $accountingRepository;

    /**
     * Constructor para inyectar el repositorio en el controlador.
     *
     * @param AccountingRepository $accountingRepository
    */
    public function __construct(AccountingRepository $accountingRepository)
    {
        $this->accountingRepository = $accountingRepository;
    }

    /**
     * Muestra la vista de recibos.
     *
     * @return View
    */
    public function receipts(): View
    {
        return view('content.accounting.receipts');
    }

    /**
     * Muestra la vista de entradas contables.
     *
     * @return View
    */
    public function entries(): View
    {
        return view('content.accounting.entries');
    }

    /**
     * Muestra la vista de una entrada contable específica.
     *
     * @return View
    */
    public function entrie(): View
    {
        return view('content.accounting.entrie');
    }

    /**
     * Muestra las estadísticas de los CFEs enviados.
     *
     * @return View
    */
    public function getSentCfes(): View
    {
        $statistics = $this->accountingRepository->getDashboardStatistics();
        return view('content.accounting.invoices.index', $statistics);
    }

    /**
     * Obtiene los datos para la tabla de recibos en formato JSON.
     *
     * @return JsonResponse
    */
    public function getInvoicesData(): JsonResponse
    {
        $invoicesData = $this->accountingRepository->getInvoicesDataForDatatables();
        return DataTables::of($invoicesData)->make(true);
    }

    /**
     * Muestra la configuración de la contabilidad.
     *
     * @return View
    */
    public function settings(): View
    {
        $pymoSetting = $this->accountingRepository->getRutSetting();
        $companyInfo = null;
        $logoUrl = null;

        if ($pymoSetting) {
            $rut = $pymoSetting->settingValue;
            $companyInfo = $this->accountingRepository->getCompanyInfo($rut);
            $logoUrl = $this->accountingRepository->getCompanyLogo($rut);
        }

        return view('content.accounting.settings', compact('pymoSetting', 'companyInfo', 'logoUrl'));
    }

    /**
     * Guarda el RUT de la empresa.
     *
     * @param SaveRutRequest $request
     * @return RedirectResponse
    */
    public function saveRut(SaveRutRequest $request): RedirectResponse
    {
        $this->accountingRepository->saveRut($request->rut);
        return redirect()->route('accounting.settings')->with('success_rut', 'RUT guardado correctamente.');
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param UploadLogoRequest $request
     * @return RedirectResponse
    */
    public function uploadLogo(UploadLogoRequest $request): RedirectResponse
    {
        $rut = $this->accountingRepository->getRutSetting()->settingValue;

        if ($this->accountingRepository->uploadCompanyLogo($rut, $request->file('logo'))) {
            return redirect()->route('accounting.settings')->with('success_logo', 'Logo actualizado correctamente.');
        }

        return redirect()->route('accounting.settings')->with('error_logo', 'Error al actualizar el logo.');
    }

    /**
     * Maneja la emisión de notas de crédito o débito.
     *
     * @param EmitNoteRequest $request
     * @param int $invoiceId
     * @return RedirectResponse
     */
    public function emitNote(EmitNoteRequest $request, int $invoiceId): RedirectResponse
    {
        try {
            $this->accountingRepository->emitNote($invoiceId, $request);
            Log::info("Nota emitida correctamente para la factura {$invoiceId}");
            return redirect()->back()->with('success', 'Nota emitida correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al emitir nota para la factura {$invoiceId}: {$e->getMessage()}");
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Descarga el PDF de un CFE.
     *
     * @param int $cfeId
     * @return mixed
    */
    public function downloadCfePdf($cfeId)
    {
        try {
            return $this->accountingRepository->getCfePdf($cfeId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    /**
     * Muestra la vista de CFEs recibidos.
     *
     * @return View
    */
    public function receivedCfes(): View
    {
        // Obtener el RUT de la tienda autenticada o de otra fuente
        $store = auth()->user()->store;
        $rut = $store->rut;

        if (!$rut) {
            return redirect()->back()->with('error', 'No se pudo obtener el RUT de la tienda.');
        }

        try {
            // Obtener las cookies para la autenticación
            $cookies = $this->accountingRepository->login();

            if (!$cookies) {
                return redirect()->back()->with('error', 'Error al iniciar sesión en el servicio PyMo.');
            }

            // Obtener los recibos desde el endpoint
            $receivedCfes = $this->accountingRepository->fetchReceivedCfes($rut, $cookies);

            if (!$receivedCfes) {
                return view('content.accounting.received_cfes', ['cfes' => []]);
            }

            // Guardar o actualizar los recibos en la base de datos
            foreach ($receivedCfes as $receivedCfe) {
                CFE::updateOrCreate(
                    [
                        'type' => $receivedCfe['CFE']['eFact']['Encabezado']['IdDoc']['TipoCFE'],
                        'serie' => $receivedCfe['CFE']['eFact']['Encabezado']['IdDoc']['Serie'],
                        'nro' => $receivedCfe['CFE']['eFact']['Encabezado']['IdDoc']['Nro']
                    ],
                    [
                        'emitionDate' => $receivedCfe['CFE']['eFact']['Encabezado']['IdDoc']['FchEmis'],
                        'total' => $receivedCfe['CFE']['eFact']['Encabezado']['Totales']['MntTotal'],
                        'received' => true,
                    ]
                );
            }

            // Obtener los CFEs actualizados de la base de datos para mostrar en la vista
            $cfes = CFE::where('recibido', true)->get();

            return view('content.accounting.received_cfes', compact('cfes'));

        } catch (\Exception $e) {
            Log::error('Error al obtener los recibos recibidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al obtener los recibos recibidos.');
        }
    }


    /**
     * Maneja la emisión de un recibo sobre una factura o eTicket existente.
     *
     * @param int $invoiceId
     * @return RedirectResponse
    */
    public function emitReceipt(int $invoiceId): RedirectResponse
    {
        try {
            $this->accountingRepository->emitReceipt($invoiceId);
            Log::info("Recibo emitido correctamente para la factura {$invoiceId}");
            return redirect()->back()->with('success', 'Recibo emitido correctamente.');
        } catch (\Exception $e) {
            Log::error("Error al emitir recibo para la factura {$invoiceId}: {$e->getMessage()}");
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
