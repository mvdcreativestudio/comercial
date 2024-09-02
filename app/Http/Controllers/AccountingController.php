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
}
