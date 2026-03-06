<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class GenericImport implements ToArray
{
    public function array(array $array)
    {
        return $array;
    }
}