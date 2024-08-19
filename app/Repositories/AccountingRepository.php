<?php

namespace App\Repositories;

use App\Models\Receipt;
use App\Models\PymoSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class AccountingRepository
{
    /**
     * Obtiene todos los recibos con las relaciones necesarias.
     *
     * @return Collection
    */
    public function getReceiptsWithRelations(): Collection
    {
      return Receipt::with('order.client', 'order.store')->get();
    }

    /**
     * Prepara los datos de los recibos para ser usados en DataTables.
     *
     * @return Collection
    */
    public function getReceiptsDataForDatatables(): Collection
    {
      $receipts = $this->getReceiptsWithRelations();

      return $receipts->map(function ($receipt) {
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
    }

    /**
     * Obtiene los comprobantes fiscales electrónicos (CFE) enviados de una empresa.
     *
     * @param string $rut
     * @param array $cookies
     * @return array|null
    */
    public function getCompanySentCfes(string $rut, array $cookies): ?array
    {
      $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
          ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sentCfes');

      if ($response->failed() || !isset($response->json()['payload']['companySentCfes'])) {
          return null;
      }

      return $response->json()['payload']['companySentCfes'];
    }

    /**
     * Obtiene la configuración del RUT de la empresa.
     *
     * @return PymoSetting|null
    */
    public function getRutSetting(): ?PymoSetting
    {
      return PymoSetting::where('settingKey', 'rut')->first();
    }

    /**
     * Guarda el RUT de la empresa en la configuración.
     *
     * @param string $rut
     * @return void
    */
    public function saveRut(string $rut): void
    {
      PymoSetting::updateOrCreate(
          ['settingKey' => 'rut'],
          ['settingValue' => $rut]
      );
    }

    /**
     * Sube el logo de la empresa.
     *
     * @param string $rut
     * @param UploadedFile $logo
     * @param array $cookies
     * @return bool
    */
    public function uploadCompanyLogo(string $rut, UploadedFile $logo): bool
    {
      $cookies = $this->login();

      if (!$cookies) {
          return false;
      }

      $logoResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
          ->attach('logo', $logo->get(), 'logo.jpg')
          ->post(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/logo');

      return $logoResponse->successful();
    }

    /**
     * Obtiene el logo de la empresa y lo guarda localmente.
     *
     * @param string $rut
     * @return string|null
    */
    public function getCompanyLogo(string $rut): ?string
    {
      $cookies = $this->login();

      if (!$cookies) {
          return null;
      }

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
     * @return array|null
    */
    public function getCompanyInfo(string $rut): ?array
    {
        $cookies = $this->login();

        if (!$cookies) {
            return null;
        }

        $companyResponse = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
            ->get(env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut);

        if ($companyResponse->failed() || !isset($companyResponse->json()['payload']['company'])) {
            return null;
        }

        return $companyResponse->json()['payload']['company'];
    }

    /**
     * Emite un CFE (eFactura o eTicket) para una orden.
     *
     * @param Order $order
     * @param string $tipoCFE
     * @param float|null $montoFactura
     * @return void
    */
    public function emitirCFE(Order $order, string $tipoCFE, ?float $montoFactura = null): void
    {
        $cookies = $this->login();

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para emitir el CFE.');
            return;
        }

        $rutSetting = PymoSetting::where('settingKey', 'rut')->first();
        if ($rutSetting) {
            $rut = $rutSetting->settingValue;
            $cfeType = $tipoCFE === 'eFactura' ? '111' : '101';
            $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/1';

            // Usar montoFactura si está definido, si no, usar el total de la orden
            $montoAFacturar = $montoFactura ?? $order->total;

            $cfeData = $this->prepararCFEData($order, $cfeType, $montoAFacturar);

            try {
                $payloadArray = [
                    'emailsToNotify' => [],
                    $cfeType => [$cfeData],
                ];

                $payload = (object)$payloadArray;

                $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                    ->asJson()
                    ->post($url, $payload);

                if ($response->successful()) {
                    Log::info('CFE emitido correctamente: ' . $response->body());

                    $responseData = $response->json();

                    foreach ($responseData['payload']['cfesIds'] as $cfe) {
                        try {
                            $receipt = Receipt::create([
                                'order_id' => $order->id,
                                'store_id' => $order->store_id,
                                'type' => $cfeType,
                                'serie' => $cfe['serie'],
                                'nro' => $cfe['nro'],
                                'caeNumber' => $cfe['caeNumber'],
                                'caeRange' => json_encode($cfe['caeRange']),
                                'caeExpirationDate' => $cfe['caeExpirationDate'],
                                'total' => $montoAFacturar,
                                'emitionDate' => $cfe['emitionDate'],
                                'sentXmlHash' => $cfe['sentXmlHash'],
                                'securityCode' => $cfe['securityCode'],
                                'qrUrl' => $cfe['qrUrl'],
                                'cfeId' => $cfe['id'],
                            ]);

                            Log::info('Receipt creado correctamente:', $receipt->toArray());
                        } catch (\Exception $e) {
                            Log::error('Error al crear Receipt: ' . $e->getMessage());
                        }
                    }
                } else {
                    Log::error('Error al emitir CFE: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Excepción al emitir CFE: ' . $e->getMessage());
            }
        } else {
            Log::error('No se encontró el RUT de la empresa para emitir el CFE.');
        }
    }

    /**
     * Prepara los datos necesarios para emitir el CFE.
     *
     * @param Order $order
     * @param string $cfeType
     * @param float $montoAFacturar
     * @return array
    */
    private function prepararCFEData(Order $order, string $cfeType, float $montoAFacturar): array
    {
      $client = $order->client;
      $products = json_decode($order->products, true);

      // Calcular proporción si se está usando un monto facturado menor al total de la orden
      $proporcion = ($montoAFacturar < $order->total) ? $montoAFacturar / $order->total : 1;

      $items = array_map(function ($product, $index) use ($proporcion) {
          $cantidadAjustada = round($product['quantity'] * $proporcion, 2); // Redondeo a dos decimales
          $montoItemAjustado = round(($product['price'] * $product['quantity']) * $proporcion, 2); // Redondeo a dos decimales

          return [
              'NroLinDet' => $index + 1, // Número de línea de detalle
              'IndFact' => 3, // Select en la interfaz, 3 - Tasa Básica (22%), 1 - Exento de Iva (0%),  2 - Tasa Mínima (10%)
              'NomItem' => $product['name'], // Nombre del producto
              'Cantidad' => $cantidadAjustada, // Cantidad del producto
              'UniMed' => 'N/A', // Unidad de medida, si no tiene usar N/A
              'PrecioUnitario' => $product['price'], // Precio unitario del producto
              'MontoItem' => $montoItemAjustado, // Monto del item
          ];
      }, $products, array_keys($products));

      $cfeData = [
          'clientEmissionId' => $order->uuid,
          'adenda' => 'Orden ' . $order->uuid . ' - MVD.',
          'IdDoc' => [
              'MntBruto' => 1,
              'FmaPago' => $order->payment_method == 'cash' ? 1 : 2 // Al facturar manualmente se podra elegir si fue credito o contado, si no asume que es constado.
          ],
          'Receptor' => [
              'TipoDocRecep' => '2', // Debe llegar 2 para RUC y 3 para CI, para eFactura siempre es 2
              'CodPaisRecep' => 'UY',
              'DocRecep' => $client->document_number ?? '123456789012', // Valor del RUC o CI
              'RznSocRecep' => $client->name . ' ' . $client->lastname, // Nombre completo del cliente o razón social
              'DirRecep' => $client->address, // Dirección del cliente
              'CiudadRecep' => $client->state, // Ciudad del cliente (Recuperar del address)
              'DeptoRecep' => $client->country, // Depto (ej Montevideo) (Recuperar del address)
          ],
          'Totales' => [
              'TpoMoneda' => 'UYU', // Moneda de la factura (quizá cambie a USD)
              // Opcional TpoCambio
              'MntNoGrv' => $montoAFacturar, // Configurado igual que en la documentación

              'MntNetoIvaTasaMin' => 0, // Configurado igual que en la documentación
              'MntNetoIVATasaBasica' => 0, // Valor total de la factura

              'IVATasaMin' => 10,
              'IVATasaBasica' => 22, // Iva Normal

              'MntIVATasaMin' => 0,
              'MntIVATasaBasica' => 0, // Redondeo a dos decimales

              'MntTotal' => $montoAFacturar, // Total a pagar
              'CantLinDet' => count($items), // Cantidad de lineas de articulos ??
              'MntPagar' => $montoAFacturar, // Total a pagar
          ],
          'Items' => $items,
      ];

      if ($cfeType === '101') { // eTicket
          $cfeData['IdDoc']['FchEmis'] = now()->toIso8601String();
      }

      return $cfeData;
    }

    /**
     * Realiza el login en el servicio externo y devuelve las cookies de la sesión.
     *
     * @return array|null
    */
    public function login(): ?array
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
     * Obtiene estadísticas para el dashboard contable.
     *
     * @return array
    */
    public function getDashboardStatistics(): array
    {
      $receipts = $this->getReceiptsWithRelations();
      $totalReceipts = $receipts->count();
      $totalIncome = $receipts->sum('total');
      $storeWithMostReceipts = $receipts->groupBy('store_id')
          ->sortByDesc(function ($group) {
              return $group->count();
          })->first();
      $storeNameWithMostReceipts = $storeWithMostReceipts ? $storeWithMostReceipts->first()->order->store->name : 'N/A';

      return compact('receipts', 'totalReceipts', 'totalIncome', 'storeNameWithMostReceipts');
    }
}
