<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CurrencyRate;

class FetchCurrenciesRates extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
    */
    protected $signature = 'fetch:currencies-rates';

    /**
     * La descripción del comando de consola.
     *
     * @var string
    */
    protected $description = 'Obtiene y actualiza las tasas de cambio de las monedas.';


    /**
     * Ejecuta el comando de consola.
     *
     * @return int
    */
    public function handle()
    {
        try {
            // Realiza la solicitud HTTP al endpoint
            $response = Http::get('https://broucurrenciesapi-cold-shadow-6325.fly.dev/currencies');

            // Verifica si la solicitud fue exitosa
            if ($response->successful()) {
                $currencies = $response->json();

                foreach ($currencies as $currency) {
                    // Reemplaza las comas con puntos y guarda los valores
                    $buyRate = $currency['compra'] === '-' ? null : str_replace(',', '.', $currency['compra']);
                    $sellRate = $currency['venta'] === '-' ? null : str_replace(',', '.', $currency['venta']);

                    // Crea o actualiza el registro en la base de datos
                    CurrencyRate::updateOrCreate(
                        ['name' => $currency['moneda'], 'date' => now()->toDateString()], // Usamos la fecha y el nombre como identificadores únicos
                        [
                            'buy' => $buyRate,
                            'sell' => $sellRate,
                        ]
                    );
                }

                $this->info('Currency rates fetched and saved successfully.');
            } else {
                $this->error('Failed to fetch data from the API.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }

        return 0;
    }
}
