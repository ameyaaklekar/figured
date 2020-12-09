<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    const TYPE_PURCHASE = 'Purchase';
    const TYPE_APPLICATION = 'Application';

    public function getInventory(Request $request) {
        $validationRule = [
            'quantity' => ['required', 'integer'] 
        ];

        $messages = [
            'required' => 'The :attribute is required.',
            'integer' => 'Please enter valid quantity'
        ];

        Validator::make($request->all(), $validationRule, $messages)->validate();

        $requestedQuantity = $request->quantity;
        $inventoryData = Inventory::getInventoryData();

        $response = array_reduce($inventoryData, function($purchaseData, $inventory) {
            if ($inventory['Type'] == self::TYPE_PURCHASE) {

                $purchaseData[] = $inventory;

            } else if ($inventory['Type'] == self::TYPE_APPLICATION) {

                $purchaseData = array_reduce($purchaseData, function($processedStock, $availableStock) use ($inventory) {

                    if (!empty($processedStock) && $processedStock['Quantity'] > 0) {

                        $processedStock['Quantity'] -= $availableStock['Quantity'];

                    } else {

                        $processedStock = $availableStock;
                        $processedStock['Quantity'] = abs($inventory['Quantity']) - $availableStock['Quantity'];

                    }

                    if ($processedStock['Quantity'] > 0) {

                        unset($availableStock);

                    } else if ($processedStock['Quantity'] <= 0) {

                        $processedStock['Quantity'] = abs($processedStock['Quantity']);

                    }

                    return $processedStock;
                }, []);
            }

            return $purchaseData;

        }, []);

        return $response;
    }
}
