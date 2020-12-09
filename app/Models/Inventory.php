<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Inventory class
 * 
 * Class to handle Inventory Data Model
 */
class Inventory extends Model
{
    use HasFactory;

    /**
     * Function to read the CSV Data source
     *
     * @return Array
     */
    public static function getInventoryData() {
        $filename = storage_path('test.csv');

        $csvData = array_map('str_getcsv', file($filename));
        array_walk($csvData, function(&$a) use ($csvData) {
            $a = array_combine($csvData[0], $a);
        });
        array_shift($csvData);
        $dateColumn = array_column($csvData, 'Date');

        array_multisort($dateColumn, SORT_ASC, $csvData);

        return $csvData;
    }    
}
