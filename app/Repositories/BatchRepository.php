<?php

namespace App\Repositories;

use App\Models\Batch;

class BatchRepository
{
    public function getAll()
    {
        return Batch::all();
    }

    public function find($id)
    {
        return Batch::findOrFail($id);
    }

    public function create(array $data)
    {
        return Batch::create($data);
    }

    public function update($id, array $data)
    {
        $batch = $this->find($id);
        $batch->update($data);
        return $batch;
    }

    public function delete($id)
    {
        $batch = Batch::find($id);
        return $batch->delete();
    }

    public function createBatches(array $data)
{
    $batches = [];
    
    foreach ($data['batches'] as $batch) {
        $batches[] = [
            'batch_number' => $batch['batch_number'],
            'quantity' => $batch['quantity'],
            'production_date' => $batch['production_date'],
            'expiration_date' => $batch['expiration_date'],
            'purchase_entries_id' => $data['purchase_entries_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    return Batch::insert($batches); // Inserta todos los lotes de una vez
}

}
