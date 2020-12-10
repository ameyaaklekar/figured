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

    const TYPE_PURCHASE = 'Purchase';
    const TYPE_APPLICATION = 'Application';

    /**
     * Function to read the CSV Data source
     *
     * @return Array
     */
    public static function getInventoryData() {
        // path for the data source
        $filename = storage_path('test.csv');

        // reads the csv data
        $csvData = array_map('str_getcsv', file($filename));

        // combines the csv data with csv header
        array_walk($csvData, function(&$a) use ($csvData) {
            $a = array_combine($csvData[0], $a);
        });

        // removes the header from the array.
        array_shift($csvData);

        // Sorts the array in ASC order based on Date (date of transaction Purchase/Applied)
        $dateColumn = array_column($csvData, 'Date');
        array_multisort($dateColumn, SORT_ASC, $csvData);

        return $csvData;
    }
    
    /**
     * Computes the total available stock from the used stock 
     * based on the date of purchase and date of used
     *
     * @param Array $inventoryData
     * @return Array
     */
    public static function getAvailableStock($inventoryData) {

        if (empty($inventoryData)) return [];

        $availableStock = [];

        foreach ($inventoryData as $inventory) {
            if ($inventory['Type'] == self::TYPE_PURCHASE) {
                /**
                 * creates an array of all the purchases before they are applied because the product purchased
                 * first should be utilised first
                 */
                $availableStock[] = $inventory;
            } else if ($inventory['Type'] == self::TYPE_APPLICATION) {
                /**
                 * loops through all the purchases done before application to calculate the correct
                 * available inventory based on the purchase date.
                 */
                foreach ($availableStock as $key => $stock) {
                    $usedQuantity = 0;
                    
                    // to verify the initial application quantity is correct
                    if (abs($inventory['Quantity']) > 0) {
                        $usedQuantity = abs($inventory['Quantity']) - $stock['Quantity'];
                    }

                    if ($usedQuantity > 0) {
                        /**
                         * if used quantity is greated than 0 that means the purchase order was less than 
                         * the used quantity and all the stock from that order is used. 
                         * hence we remove the stock from the available stock and update the purchase order
                         * quantity with the remaining quantity.
                         */
                        unset($availableStock[$key]);
                        $inventory['Quantity'] = $usedQuantity;
                    } else if ($usedQuantity < 0) {
                        /**
                         * If used quantity value is less than that means the applied quantity was 
                         * less than the purchased quantity and didnt utilised the whole purchased lot. Hence we 
                         * update the remaining quantity of the stock in the availble stock.
                         */
                        $inventory['Quantity'] = 0;
                        $stock['Quantity'] = abs($usedQuantity);
                        $availableStock[$key] = $stock;
                    }
                }
            }            
        }

        return $availableStock;
    }
}
