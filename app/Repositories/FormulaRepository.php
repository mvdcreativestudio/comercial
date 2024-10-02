<?php

namespace App\Repositories;

use App\Models\Formula;

class FormulaRepository
{
    public function getAll()
    {
        return Formula::all();
    }

    public function find($id)
    {
        return Formula::findOrFail($id);
    }

    public function create(array $data)
    {
        return Formula::create($data);
    }

    public function update($id, array $data)
    {
        $formula = $this->find($id);
        $formula->update($data);
        return $formula;
    }

    public function delete($id)
    {
        $formula = Formula::find($id);
        return $formula->delete();
    }
}
