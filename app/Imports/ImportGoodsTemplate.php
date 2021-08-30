<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImportGoodsTemplate
 *
 * @author singh
 */
namespace App\Imports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
class ImportGoodsTemplate implements ToCollection
{
    public $rows;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $this->rows = $collection;
    }
}
