<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ScanntechService extends PaymentService
{
    private $scanntechAuthService;

    public function __construct()
    {
        $this->scanntechAuthService = app(ScanntechAuthService::class);
    }

    /**
     * Procesa la transacción de compra.
     *
     * @param array $transactionData
     * @return array
     */
    public function process(array $transactionData)
    {
        $formattedData = $this->formatData($transactionData);

        // 1. Iniciar la transacción
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->scanntechAuthService->getAccessToken(),
            'Content-Type' => 'application/json',
        ])->post('http://200.40.123.21:35000/rest/v2/postPurchase', $formattedData);

        if (!$response->successful()) {
            return ['error' => 'Error al iniciar la transacción.'];
        }

        // Extraer transactionId si está disponible
        $transactionId = $response->json('transactionId') ?? null;

        if (!$transactionId) {
            return ['error' => 'No se pudo obtener el ID de la transacción.'];
        }

        // 2. Consultar el estado de la transacción hasta obtener el resultado final
        return $this->pollTransactionState($transactionId);
    }

    /**
     * Consulta el estado de la transacción repetidamente hasta obtener el resultado final.
     *
     * @param string $transactionId
     * @return array
     */
    private function pollTransactionState(string $transactionId)
    {
        $attempts = 0;
        $maxAttempts = 30; // Máximo de intentos para no quedar en un bucle infinito

        while ($attempts < $maxAttempts) {
            sleep(2); // Esperar 2 segundos entre cada consulta

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->scanntechAuthService->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->get('http://200.40.123.21:35000/rest/v2/getTransactionState', [
                'transactionId' => $transactionId,
            ]);

            if ($response->successful()) {
                $transactionState = $response->json();

                // Revisar el estado de la transacción
                if (isset($transactionState['responseCode']) && $transactionState['responseCode'] !== 'PENDING') {
                    // Finalizó la transacción
                    return $transactionState;
                }
            } else {
                return ['error' => 'Error al consultar el estado de la transacción.'];
            }

            $attempts++;
        }

        return ['error' => 'Tiempo de espera excedido al consultar el estado de la transacción.'];
    }

    protected function formatData(array $data): array
    {
        // Formateo específico para los datos enviados a SCANNSAE
        $data['needToReadCard'] = false; // Asegurarse de que la bandera esté apagada
        return $data;
    }
}
