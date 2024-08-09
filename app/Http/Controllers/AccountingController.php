<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PymoSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Receipt;
use Yajra\DataTables\DataTables;

class AccountingController extends Controller
{
    /**
     * Muestra la vista de recibos.
     *
     * @return \Illuminate\View\View
     */
    public function receipts()
    {
        return view('content.accounting.receipts');
    }

    /**
     * Muestra la vista de entradas contables.
     *
     * @return \Illuminate\View\View
     */
    public function entries()
    {
        return view('content.accounting.entries');
    }

    /**
     * Muestra la vista de una entrada contable específica.
     *
     * @return \Illuminate\View\View
     */
    public function entrie()
    {
        return view('content.accounting.entrie');
    }

    /**
     * Obtiene y devuelve los sobres enviados.
     *
     * @return \Illuminate\View\View
    */
    public function getSentCfes()
    {
        // Obtener los recibos con la información relacionada
        $receipts = Receipt::with('order.client', 'order.store')->get();

        // Calcular el total de facturas emitidas
        $totalReceipts = $receipts->count();

        // Calcular el ingreso total de todas las facturas
        $totalIncome = $receipts->sum('total');

        // Identificar la tienda con más emisiones
        $storeWithMostReceipts = $receipts->groupBy('store_id')
            ->sortByDesc(function ($group) {
                return $group->count();
            })->first();

        // Nombre de la tienda con más emisiones, si existe
        $storeNameWithMostReceipts = $storeWithMostReceipts ? $storeWithMostReceipts->first()->order->store->name : 'N/A';

        return view('content.accounting.receipts.index', compact('receipts', 'totalReceipts', 'totalIncome', 'storeNameWithMostReceipts'));
    }

    /**
     * Obtiene los datos para la tabla de recibos en formato JSON.
     *
     * @return \Illuminate\Http\JsonResponse
    */
    public function getReceiptsData()
    {
        // Obtener todos los recibos con la información relacionada
        $receipts = Receipt::with('order.store', 'order.client')->get();

        // Transformar los datos para DataTables
        $receiptsData = $receipts->map(function ($receipt) {
          return [
              'id' => $receipt->id,
              'store_name' => $receipt->order->store->name ?? 'N/A',
              'client_name' => $receipt->order->client->name ?? 'N/A',
              'client_email' => $receipt->order->client->email ?? 'N/A',
              'client_lastname' => $receipt->order->client->lastname ?? 'N/A',
              'date' => $receipt->emitionDate,
              'type' => $receipt->type == 101 ? 'eTicket' : 'eFactura',
              'currency' => $receipt->order->currency ?? 'UYU',
              'total' => $receipt->total,
              'qrUrl' => $receipt->qrUrl,
              'order_uuid' => $receipt->order->uuid,
              'serie' => $receipt->serie,
              'cfeId' => $receipt->cfeId,
              'nro' => $receipt->nro,
              'caeNumber' => $receipt->caeNumber,
              'caeRange' => $receipt->caeRange,
              'caeExpirationDate' => $receipt->caeExpirationDate,
              'sentXmlHash' => $receipt->sentXmlHash,
              'securityCode' => $receipt->securityCode,
          ];
      });

        // Retornar la respuesta en formato JSON
        return DataTables::of($receiptsData)->make(true);
    }


    /**
     * Obtiene los sobres enviados de la empresa.
     *
     * @param string $rut
     * @param array $cookies
     * @return array|null
    */
    private function getCompanySentCfes(string $rut, array $cookies): ?array
    {
        $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
            ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sentCfes');

        if ($response->failed() || !isset($response->json()['payload']['companySentCfes'])) {
            return null;
        }

        return $response->json()['payload']['companySentCfes'];
    }

    /**
     * Muestra la configuración de la contabilidad.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $pymoSetting = PymoSetting::where('settingKey', 'rut')->first();
        $companyInfo = null;
        $logoUrl = null;

        if ($pymoSetting) {
            $rut = $pymoSetting->settingValue;
            $cookies = $this->login();

            if ($cookies) {
                $companyInfo = $this->getCompanyInfo($rut, $cookies);
                $logoUrl = $this->getCompanyLogo($rut, $cookies);
            }
        }

        return view('content.accounting.settings', compact('pymoSetting', 'companyInfo', 'logoUrl'));
    }

    /**
     * Guarda el RUT de la empresa.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveRut(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|max:255',
        ]);

        PymoSetting::updateOrCreate(
            ['settingKey' => 'rut'],
            ['settingValue' => $request->rut]
        );

        return redirect()->route('accounting.settings')->with('success_rut', 'RUT guardado correctamente.');
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|file',
        ]);

        $rut = PymoSetting::where('settingKey', 'rut')->first()->settingValue;
        $cookies = $this->login();

        if ($cookies) {
            $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->attach('logo', $request->file('logo')->get(), 'logo.jpg')
                ->post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/logo');

            if ($logoResponse->successful()) {
                return redirect()->route('accounting.settings')->with('success_logo', 'Logo actualizado correctamente.');
            }
        }

        return redirect()->route('accounting.settings')->with('error_logo', 'Error al actualizar el logo.');
    }

    /**
     * Realiza el login y devuelve las cookies de la sesión.
     *
     * @return array|null
     */
    private function login(): ?array
    {
        $loginResponse = Http::post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/login', [
            'email' => env('PYMO_USER'),
            'password' => env('PYMO_PASSWORD'),
        ]);

        if ($loginResponse->failed()) {
            return null;
        }

        $cookies = $loginResponse->cookies();
        $cookieJar = [];

        foreach ($cookies as $cookie) {
            $cookieJar[$cookie->getName()] = $cookie->getValue();
        }

        return $cookieJar;
    }

    /**
     * Obtiene el logo de la empresa y lo guarda localmente.
     *
     * @param string $rut
     * @param array $cookies
     * @return string|null
     */
    private function getCompanyLogo(string $rut, array $cookies): ?string
    {
        $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
            ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/logo');

        if ($logoResponse->failed()) {
            return null;
        }

        return $this->saveLogoLocally($logoResponse->body());
    }

    /**
     * Guarda la imagen del logo en almacenamiento local.
     *
     * @param string $imageContent
     * @return string
     */
    private function saveLogoLocally(string $imageContent): string
    {
        $logoPath = 'public/assets/img/logos/company_logo.jpg';
        Storage::put($logoPath, $imageContent);

        return Storage::url($logoPath);
    }

    /**
     * Obtiene la información de la empresa.
     *
     * @param string $rut
     * @param array $cookies
     * @return array|null
     */
    private function getCompanyInfo(string $rut, array $cookies): ?array
    {
        $companyResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
            ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut);

        if ($companyResponse->failed() || !isset($companyResponse->json()['payload']['company'])) {
            return null;
        }

        return $companyResponse->json()['payload']['company'];
    }
}
