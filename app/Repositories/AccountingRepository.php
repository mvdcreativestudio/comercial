<?php

namespace App\Repositories;

use App\Http\Requests\EmitNoteRequest;
use App\Models\CFE;
use App\Models\PymoSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Models\Order;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\CurrencyRate;

class AccountingRepository
{
    /**
     * Obtiene todos los recibos con las relaciones necesarias.
     *
     * @return Collection
    */
    public function getInvoicesWithRelations(): Collection
    {
        $validTypes = [101, 102, 103, 111, 112, 113]; // Tipos válidos de CFE, eTickets, eFacturas, Notas de Crédito y Notas de Débito respectivamente

        if (auth()->user()->can('view_all_accounting')) {
            return CFE::with('order.client', 'order.store')
                ->whereIn('type', $validTypes)
                ->get();
        }

        return CFE::with('order.client', 'order.store')
            ->whereIn('type', $validTypes)
            ->whereHas('order.store', function ($query) {
                $query->where('id', auth()->user()->store_id);
            })
            ->get();
    }

    /**
     * Prepara los datos de los recibos para ser usados en DataTables.
     *
     * @return Collection
    */
    public function getInvoicesDataForDatatables(): Collection
    {
      $invoices = $this->getInvoicesWithRelations();

      return $invoices->map(function ($invoice) {
          $typeCFEs = [
            101 => 'eTicket',
            102 => 'eTicket - Nota de Crédito',
            103 => 'eTicket - Nota de Débito',
            111 => 'eFactura',
            112 => 'eFactura - Nota de Crédito',
            113 => 'eFactura - Nota de Débito',
          ];

          return [
              'id' => $invoice->id,
              'store_name' => $invoice->order->store->name ?? 'N/A',
              'client_name' => $invoice->order->client->name ?? 'N/A',
              'client_email' => $invoice->order->client->email ?? 'N/A',
              'client_lastname' => $invoice->order->client->lastname ?? 'N/A',
              'date' => $invoice->emitionDate,
              'order_id' => $invoice->order->id,
              'type' => $typeCFEs[$invoice->type] ?? 'N/A',
              'currency' => 'USD',
              'total' => $invoice->total,
              'qrUrl' => $invoice->qrUrl,
              'order_uuid' => $invoice->order->uuid,
              'serie' => $invoice->serie,
              'cfeId' => $invoice->cfeId,
              'nro' => $invoice->nro,
              'balance' => $invoice->balance,
              'caeNumber' => $invoice->caeNumber,
              'caeRange' => $invoice->caeRange,
              'caeExpirationDate' => $invoice->caeExpirationDate,
              'sentXmlHash' => $invoice->sentXmlHash,
              'securityCode' => $invoice->securityCode,
              'reason' => $invoice->reason,
              'associated_id' => $invoice->main_cfe_id,
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
     * @param float|null $amountToBill
     * @param int|null $payType
     * @return void
    */
    public function emitCFE(Order $order, ?float $amountToBill = null, ?int $payType = 1): void
    {
        $cookies = $this->login();

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para emitir el CFE.');
            return;
        }

        $rutSetting = PymoSetting::where('settingKey', 'rut')->first();
        if ($rutSetting) {
            $rut = $rutSetting->settingValue;
            $cfeType = $order->doc_type == 2 ? '111' : '101';
            $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/1';

            $amountToBill = $amountToBill ?? $order->total;

            $cfeData = $this->prepareCFEData($order, $cfeType, $amountToBill, $payType);

            Log::info('Datos del CFE:', $cfeData);

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
                            $invoice = CFE::create([
                                'order_id' => $order->id,
                                'store_id' => $order->store_id,
                                'type' => $cfeType,
                                'serie' => $cfe['serie'],
                                'nro' => $cfe['nro'],
                                'caeNumber' => $cfe['caeNumber'],
                                'caeRange' => json_encode($cfe['caeRange']),
                                'caeExpirationDate' => $cfe['caeExpirationDate'],
                                'total' => $amountToBill,
                                'balance' => $amountToBill,
                                'emitionDate' => $cfe['emitionDate'],
                                'sentXmlHash' => $cfe['sentXmlHash'],
                                'securityCode' => $cfe['securityCode'],
                                'qrUrl' => $cfe['qrUrl'],
                                'cfeId' => $cfe['id'],
                            ]);

                            Log::info('Receipt creado correctamente:', $invoice->toArray());
                        } catch (\Exception $e) {
                            throw new \Exception('Error al crear el recibo: ' . $e->getMessage());
                        }
                    }
                } else {
                    Log::error('Error al emitir CFE: ' . $response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception('Error al emitir CFE: ' . $e->getMessage());
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
     * @param float $amountToBill
     * @param int $payType
     * @return array
    */
    private function prepareCFEData(Order $order, string $cfeType, float $amountToBill, int $payType): array
    {
        $client = $order->client;
        $products = is_string($order->products) ? json_decode($order->products, true) : $order->products;

        $usdRate = CurrencyRate::where('name', 'Dólar')->orderBy('date', 'desc')->first();

        if ($usdRate) {
            $exchangeRate = (float) str_replace(',', '.', $usdRate->sell);
        } else {
            throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        }

        // Calcular proporción si se está usando un monto facturado menor al total de la orden
        $proporcion = ($amountToBill < $order->total) ? $amountToBill / $order->total : 1;

        $items = array_map(function ($product, $index) use ($proporcion, $order) {
            $cantidadAjustada = round($product['quantity'] * $proporcion, 2); // Redondeo a dos decimales
            $montoItemAjustado = round(($product['price'] * $product['quantity']) * $proporcion, 2); // Redondeo a dos decimales
            // Log de Product name
            Log::info('Product name: ' . $product['name']);
            return [
                'NroLinDet' => $index + 1, // Número de línea de detalle
                'IndFact' => 3, // Valor de 'IndFact' de la orden -- SI ES RUC ESTA EXENTO DE IVA ?
                'NomItem' => 'Venta PDV', // Nombre del producto
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
                'FmaPago' => $payType // Al facturar manualmente se puede elegir si fue crédito o contado, si no asume que es contado.
            ],
            'Receptor' => [
                'TipoDocRecep' => 3, // Tipo de documento receptor
                'CodPaisRecep' => 'UY',
                'DocRecep' => 12345678, // Documento receptor (RUC o CI)
                'RznSocRecep' => $client->name . ' ' . $client->lastname, // Nombre completo del cliente o razón social
                'DirRecep' => $client->address, // Dirección del cliente
                'CiudadRecep' => $client->city, // Ciudad del cliente PASA A LA TABLA DE CLIENTE
                'DeptoRecep' => $client->state, // Departamento del cliente PASA A LA TABLA DE CLIENTE
            ],
            'Totales' => [
                'TpoMoneda' => 'USD', // Moneda de la factura (quizá cambie a USD)
                'TpoCambio' => $exchangeRate, // Tipo de cambio
                'MntNoGrv' => $amountToBill, // Configurado igual que en la documentación
                'MntNetoIvaTasaMin' => 0, // Configurado igual que en la documentación
                'MntNetoIVATasaBasica' => 0, // Valor total de la factura
                'IVATasaMin' => 10,
                'IVATasaBasica' => 22, // IVA Normal
                'MntIVATasaMin' => 0,
                'MntIVATasaBasica' => 0, // Redondeo a dos decimales
                'MntTotal' => $amountToBill, // Total a pagar
                'CantLinDet' => count($items), // Cantidad de líneas de artículos
                'MntPagar' => $amountToBill, // Total a pagar
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
      $invoices = $this->getInvoicesWithRelations();
      $totalReceipts = $invoices->count();
      $totalIncome = $invoices->sum('total');
      $storeWithMostReceipts = $invoices->groupBy('store_id')
          ->sortByDesc(function ($group) {
              return $group->count();
          })->first();
      $storeNameWithMostReceipts = $storeWithMostReceipts ? $storeWithMostReceipts->first()->order->store->name : 'N/A';

      return compact('invoices', 'totalReceipts', 'totalIncome', 'storeNameWithMostReceipts');
    }


    /**
     * Emite una nota de crédito o débito para una factura o eTicket existente.
     *
     * @param int $invoiceId
     * @param EmitNoteRequest $request
     * @return void
     * @throws Exception
    */
    public function emitNote(int $invoiceId, EmitNoteRequest $request): void
    {
        $cookies = $this->login();

        if (!$cookies) {
            throw new \Exception('No se pudo iniciar sesión para emitir la nota.');
        }

        $rutSetting = PymoSetting::where('settingKey', 'rut')->first();
        if (!$rutSetting) {
            throw new \Exception('No se encontró el RUT de la empresa para emitir la nota.');
        }

        $rut = $rutSetting->settingValue;

        $invoice = CFE::findOrFail($invoiceId);

        // Validar que el tipo sea eFactura (111) o eTicket (101)
        if (!in_array($invoice->type, [101, 111])) {
            throw new \Exception('No se puede emitir una nota sobre este tipo de documento.');
        }

        $noteType = $request->noteType;
        $noteAmount = $request->noteAmount;
        $reason = $request->reason;

        // Validar tipo de documento para eFactura (111)
        if ($invoice->type == 111) {
            $orderDocType = $invoice->order->doc_type;
            $orderDocument = $invoice->order->document;

            if ($orderDocType == 2 && strlen($orderDocument) !== 12) { // RUC
                throw new \Exception('El RUC debe tener 12 caracteres.');
            } elseif ($orderDocType == 3 && strlen($orderDocument) !== 8) { // CI
                throw new \Exception('La CI debe tener 8 caracteres.');
            }
        }

        $cfeType = match ($invoice->type) {
            101 => $noteType === 'credit' ? '102' : '103',
            111 => $noteType === 'credit' ? '112' : '113',
            default => throw new \Exception('Tipo de CFE no soportado para notas.')
        };

        // Validar el balance del CFE principal
        $currentBalance = $invoice->balance ?? 0;

        if ($noteType === 'credit' && $noteAmount > $currentBalance) {
            throw new \Exception('El monto de la nota de crédito no puede ser mayor que el balance actual.');
        }

        // Calcular el nuevo balance
        $newBalance = ($noteType === 'credit') ? $currentBalance - $noteAmount : $currentBalance + $noteAmount;

        if ($newBalance < 0) {
            throw new \Exception('El balance no puede ser negativo.');
        }

        // Emitir la nota y preparar los datos
        $notaData = $this->prepareNoteData($invoice, $noteAmount, $reason, $noteType);

        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/sendCfes/1';

        try {
            $payloadArray = [
                'emailsToNotify' => [],
                $cfeType => [$notaData],
            ];

            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->post($url, (object)$payloadArray);

            if ($response->successful()) {
                Log::info('Nota emitida correctamente: ' . $response->body());

                $responseData = $response->json();
                foreach ($responseData['payload']['cfesIds'] as $cfe) {
                    // Crear el nuevo CFE (Nota)
                    $newCfe = CFE::create([
                        'order_id' => $invoice->order_id,
                        'store_id' => $invoice->store_id,
                        'type' => $cfeType,
                        'serie' => $cfe['serie'],
                        'nro' => $cfe['nro'],
                        'caeNumber' => $cfe['caeNumber'],
                        'caeRange' => json_encode($cfe['caeRange']),
                        'caeExpirationDate' => $cfe['caeExpirationDate'],
                        'total' => $noteAmount,
                        'emitionDate' => $cfe['emitionDate'],
                        'sentXmlHash' => $cfe['sentXmlHash'],
                        'securityCode' => $cfe['securityCode'],
                        'qrUrl' => $cfe['qrUrl'],
                        'cfeId' => $cfe['id'],
                        'reason' => $reason,
                        'main_cfe_id' => $invoice->id,
                    ]);

                    // Actualizar el balance del CFE principal
                    $invoice->balance = $newBalance;
                    $invoice->save();
                }
            } else {
                throw new \Exception('Error al emitir nota: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Excepción al emitir nota: ' . $e->getMessage());
            throw new \Exception('Error al emitir nota: ' . $e->getMessage());
        }
    }

    /**
     * Prepara los datos necesarios para emitir una nota de crédito o débito.
     *
     * @param CFE $invoice
     * @param float $noteAmount
     * @param string $reason
     * @param string $noteType
     * @return array
    */
    private function prepareNoteData(CFE $invoice, float $noteAmount, string $reason, string $noteType): array
    {
        $order = $invoice->order;

        $usdRate = CurrencyRate::where('name', 'Dólar')->orderBy('date', 'desc')->first();

        if ($usdRate) {
            $exchangeRate = (float) str_replace(',', '.', $usdRate->sell);
        } else {
            throw new \Exception('No se encontró el tipo de cambio para el dólar.');
        }

        $notaData = [
            'clientEmissionId' => $order->uuid,
            'adenda' => $reason,
            'IdDoc' => [
                'FchEmis' => now()->toIso8601String(),
                'FmaPago' => '1',
            ],
            'Receptor' => [
                'TipoDocRecep' => 3,
                'CodPaisRecep' => 'UY',
                'PaisRecep' => 'Uruguay',
                'DocRecep' => 12345678,
                'RznSocRecep' => $order->client->name . ' ' . $order->client->lastname,
                'DirRecep' => $order->client->address,
                'CiudadRecep' => $order->client->city,
                'DeptoRecep' => $order->client->state,
                'CompraID' => null
            ],
            'Totales' => [
                'TpoMoneda' => 'USD',
                'TpoCambio' => $exchangeRate,
                'MntTotal' => $noteAmount,
                'CantLinDet' => 1,
                'MntPagar' => $noteAmount
            ],
            'Referencia' => [
                [
                    'NroLinRef' => '1',
                    'IndGlobal' => '1',
                    'TpoDocRef' => $invoice->type,
                    'Serie' => $invoice->serie ?? 'A',
                    'NroCFERef' => $invoice->nro,
                    'RazonRef' => $reason,
                    'FechaCFEref' => $invoice->emitionDate->toIso8601String()
                ]
            ],
            'Items' => [
                [
                    'NroLinDet' => '1',
                    'IndFact' => 6,
                    'NomItem' => 'Nota de ' . (ucfirst($noteType) == 'credit' ? 'Crédito' : 'Débito') . ' - Ajuste',
                    'Cantidad' => '1',
                    'UniMed' => 'N/A',
                    'PrecioUnitario' => $noteAmount,
                    'MontoItem' => $noteAmount,
                ]
            ],
            'Emisor' => [
                'GiroEmis' => 'Chelato'
            ]
        ];

        if ($invoice->type == 111) {
            $notaData['IdDoc'] = array_merge($notaData['IdDoc'], [
                'ViaTransp' => '8',
                'ClauVenta' => 'N/A',
                'ModVenta' => '90'
            ]);
        }

        return $notaData;
    }

    /**
     * Obtiene el PDF de un CFE (eFactura o eTicket) para una orden específica.
     *
     * @param int $cfeId
     * @return Response
     * @throws Exception
    */
    public function getCfePdf(int $cfeId): Response
    {
        $cfe = CFE::findOrFail($cfeId);

        // Iniciar sesión para obtener cookies
        $cookies = $this->login();

        if (!$cookies) {
            Log::error('No se pudo iniciar sesión para obtener el PDF del CFE.');
            throw new \Exception('No se pudo iniciar sesión para obtener el PDF del CFE.');
        }

        $rutSetting = PymoSetting::where('settingKey', 'rut')->first();
        if (!$rutSetting) {
            Log::error('No se encontró el RUT de la empresa para obtener el PDF del CFE.');
            throw new \Exception('No se encontró el RUT de la empresa para obtener el PDF del CFE.');
        }

        // Construir la URL para obtener el PDF
        $rut = $rutSetting->settingValue;
        $cfeId = $cfe->cfeId;
        $url = env('PYMO_HOST') . ':' . env('PYMO_PORT') . '/' . env('PYMO_VERSION') . '/companies/' . $rut . '/invoices?id=' . $cfeId;

        try {
            // Hacer la solicitud para obtener el PDF
            $response = Http::withCookies($cookies, parse_url(env('PYMO_HOST'), PHP_URL_HOST))
                ->asJson()
                ->get($url);

            if ($response->successful()) {
                $pdfContent = $response->body();

                // Enviar el PDF al navegador
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="CFE_' . $cfeId . '.pdf"');
            } else {
                Log::error('Error al obtener el PDF del CFE: ' . $response->body());
                throw new \Exception('Error al obtener el PDF del CFE.');
            }
        } catch (\Exception $e) {
            Log::error('Excepción al obtener el PDF del CFE: ' . $e->getMessage());
            throw new \Exception('Error al obtener el PDF del CFE: ' . $e->getMessage());
        }
    }
}
