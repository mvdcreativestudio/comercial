<?php

namespace App\Repositories;

use App\Models\Packaging;

class PackagingRepository
{
    public function getAll()
    {
        return Packaging::all();
    }

    public function find($id)
    {
        return Packaging::findOrFail($id);
    }

    public function create(array $data)
    {
        return Packaging::create($data);
    }

    public function update($id, array $data)
    {
        $packaging = $this->find($id);
        $packaging->update($data);
        return $packaging;
    }

    public function delete($id)
    {
        $packaging = Packaging::find($id);
        return $packaging->delete();
    }
}
