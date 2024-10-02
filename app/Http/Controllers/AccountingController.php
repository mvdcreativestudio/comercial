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
use App\Models\Store;
use Illuminate\Http\Request;

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
        return view('content.accounting.entries.index');
    }

    /**
     * Muestra la vista de una entrada contable específica.
     *
     * @return View
    */
    public function entrie(): View
    {
        return view('content.accounting.entries.entry-details.index');
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
        return redirect()->back()->with('success_rut', 'RUT guardado correctamente.');
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param UploadLogoRequest $request
     * @return RedirectResponse
    */
    public function uploadLogo(UploadLogoRequest $request): RedirectResponse
    {
        if ($this->accountingRepository->uploadCompanyLogo($request->store_id, $request->file('logo'))) {
            return redirect()->back()->with('success_logo', 'Logo actualizado correctamente.');
        }

        return redirect()->back()->with('error_logo', 'Error al actualizar el logo.');
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
            $cookies = $this->accountingRepository->login($store);

            if (!$cookies) {
                return redirect()->back()->with('error', 'Error al iniciar sesión en el servicio PyMo.');
            }

            // Obtener los recibos desde el endpoint
            $receivedCfes = $this->accountingRepository->fetchReceivedCfes($rut, $cookies);

            Log::info('Recibos recibidos: ' . json_encode($receivedCfes));

            if (!$receivedCfes) {
                return view('content.accounting.received_cfes', ['cfes' => []]);
            }

            foreach ($receivedCfes as $receivedCfe) {
                // Verificar si la estructura del recibo es correcta y contiene la clave 'eFact'
                if (!isset($receivedCfe['CFE']['eFact'])) {
                    Log::error('El recibo no tiene la estructura esperada: ' . json_encode($receivedCfe));
                    continue; // Omitir este recibo y pasar al siguiente
                }

                Log::info('Recibo recibido: ' . json_encode($receivedCfe));

                // Extraer los datos del recibo desde la respuesta
                $idDoc = $receivedCfe['CFE']['eFact']['Encabezado']['IdDoc'];
                $totales = $receivedCfe['CFE']['eFact']['Encabezado']['Totales'];
                $emisor = $receivedCfe['CFE']['eFact']['Encabezado']['Emisor'];
                $receptor = $receivedCfe['CFE']['eFact']['Encabezado']['Receptor'];
                $caeData = $receivedCfe['CFE']['CAEData'] ?? [];

                $cfeData = [
                    'type' => $idDoc['TipoCFE'],
                    'serie' => $idDoc['Serie'],
                    'nro' => $idDoc['Nro'],
                    'caeNumber' => $caeData['CAE_ID'] ?? null,
                    'caeRange' => json_encode([
                        'DNro' => $caeData['DNro'] ?? null,
                        'HNro' => $caeData['HNro'] ?? null,
                    ]),
                    'caeExpirationDate' => $caeData['FecVenc'] ?? null,
                    'total' => $totales['MntTotal'] ?? null,
                    'status' => $receivedCfe['cfeStatus'] ?? 'PENDING_REVISION',
                    'balance' => $totales['MntPagar'] ?? 0,
                    'received' => true,
                    'emitionDate' => $idDoc['FchEmis'] ?? null,
                    'sentXmlHash' => $receivedCfe['Signature']['DigestValue'] ?? null,
                    'securityCode' => $receivedCfe['Signature']['SignatureValue'] ?? null, // Ajustar según lo que consideres "código de seguridad"
                    'qrUrl' => null, // Ajustar si existe un valor de QR
                    'cfeId' => $receivedCfe['_id'] ?? null,
                    'reason' => null,
                    'store_id' => $store->id,
                    'main_cfe_id' => null,
                    'is_receipt' => ($idDoc['TipoCFE'] == '111') ? true : false,
                ];

                // Actualizar o crear el CFE en la base de datos
                CFE::updateOrCreate(
                    [
                        'type' => $cfeData['type'],
                        'serie' => $cfeData['serie'],
                        'nro' => $cfeData['nro'],
                    ],
                    $cfeData
                );
            }

            // Obtener los CFEs actualizados de la base de datos para mostrar en la vista
            $cfes = CFE::where('received', true)->get();

            return view('content.accounting.received_cfes', compact('cfes'));

        } catch (\Exception $e) {
            Log::error('Error al obtener los recibos recibidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al obtener los recibos recibidos.');
        }
    }


    public function getReceivedCfesData()
    {
        $validTypes = [101, 102, 103, 111, 112, 113]; // Tipos válidos de CFE

        // Si el usuario tiene permisos para ver toda la contabilidad
        if (auth()->user()->can('view_all_accounting')) {
            $cfes = CFE::with('order.client', 'order.store')
                ->whereIn('type', $validTypes)
                ->where('received', true)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Filtra por la tienda del usuario autenticado si no tiene permisos para ver todo
            $cfes = CFE::with('order.client', 'order.store')
                ->whereIn('type', $validTypes)
                ->whereHas('order.store', function ($query) {
                    $query->where('id', auth()->user()->store_id);
                })
                ->where('received', true)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Formatear la colección de datos para el DataTable
        $formattedCfes = $cfes->map(function ($cfe) {
            return [
                'id' => $cfe->id,
                'issuer_name' => $cfe->order->store->name ?? 'N/A',
                'emition_date' => $cfe->emitionDate,
                'total' => $cfe->total,
                'currency' => 'UYU', // Cambiar a la moneda que uses
                'reason' => $cfe->reason ?? 'N/A',
                'cfeId' => $cfe->id,
                'serie' => $cfe->serie,
                'nro' => $cfe->nro,
                'caeNumber' => $cfe->caeNumber,
                'caeRange' => $cfe->caeRange,
                'caeExpirationDate' => $cfe->caeExpirationDate,
                'sentXmlHash' => $cfe->sentXmlHash,
                'securityCode' => $cfe->securityCode,
                'qrUrl' => $cfe->qrUrl,
                'actions' => $this->getActionButtons($cfe)
            ];
        });

        return response()->json(['data' => $formattedCfes]);
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

    /**
      * Maneja la llegada de un webhook de PyMo.
      *
      * @param Request $request
      * @return void
    */
    public function webhook(Request $request): void
    {
        $data = $request->all(); // Obtener los datos del webhook
        
        Log::info('Recibiendo webhook');
    
        $type = $data['type']; // Obtener el tipo de webhook
        $urlToCheck = $data['url_to_check']; // Obtener la URL a la que hacer la petición
    
        switch ($type) { // Según el tipo de webhook
            case 'CFE_STATUS_CHANGE': // Si es un cambio de estado de CFE
                // Extraer el RUT y la sucursal usando expresiones regulares
                preg_match('/\/companies\/(\d+)\/sentCfes\/(\d+)/', $urlToCheck, $matches);
    
                if (isset($matches[1]) && isset($matches[2])) {
                    $rut = $matches[1];         // Primer grupo de captura es el RUT
                    $branchOffice = $matches[2]; // Segundo grupo de captura es la sucursal
    
                    Log::info('Rut de la tienda Webhook: ' . $rut);
                    Log::info('Branch Office Webhook: ' . $branchOffice);
    
                    $this->accountingRepository->checkCfeStatus($rut, $branchOffice, $urlToCheck);
                } else {
                    Log::info('No se pudieron extraer el RUT y la sucursal de la URL.');
                }
                break;
    
            default:
                Log::info('Invalid request');
                return;
        }
    }
}
