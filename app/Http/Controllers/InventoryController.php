<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * InventoryController class
 * 
 * Handles the api requests for Inventory
 */
class InventoryController extends Controller
{

    /**
     * Accepts "product/value" request. Computes the available stock and
     * returns the value for the requested quantity
     *
     * @param Request $request
     * @return void
     */
    public function getProductValue(Request $request) {
        $validationRule = [
            'quantity' => ['required', 'integer'] 
        ];

        $messages = [
            'required' => 'The :attribute is required.',
            'integer' => 'Please enter valid quantity'
        ];

        // Request Validation
        Validator::make($request->all(), $validationRule, $messages)->validate();

        $requestedQuantity = (int) $request->quantity;

        // if requested quantity is 0, we retun an 400 response.
        if ($requestedQuantity == 0) return response(['success' => false, 'message' => 'Invalid Requested quantity'], 400);

        //Gets the inventory data from the datasource
        $inventoryData = Inventory::getInventoryData();
        
        //Gets the available stock from all the purchases and application based on the dates
        $availableStock = Inventory::getAvailableStock($inventoryData);

        //setting the initial requested value to zero.
        $requestedProductValue = 0;
        
        // In case there is not data in the datasource or database 
        if (empty($availableStock)) return response(['success' => true, 'data' => [], 'message' => 'No Stock Available'], 200);

        /**
         * we are getting the total stock quantity available regardless the purchase date,
         * to verify if the requested quantity is available.
         */
        $totalAvailableQuantity = array_reduce(array_column($availableStock, 'Quantity'), 
            function ($totalQuantity, $stock) {
                return $totalQuantity += $stock;
            }, 0);

        // if requested quantity is greater than the total available quantity, we retun an 400 response.
        if ($requestedQuantity > $totalAvailableQuantity) return response(['success' => false, 'message' => 'Requested quantity exceeds the total available quantity of '. $totalAvailableQuantity], 400);

        /**
         * we loop through the available stock to calculate the requested product value 
         * based on the value at which the stock was purchased.
         */
        foreach ($availableStock as $stock) {
            // if requested quantity is less than zero which means the amount has been calculated.
            if ($requestedQuantity < 0) break;

            if ($requestedQuantity <= $stock['Quantity']) {
                /**
                 * Since requested quantity is greater than the purchased quantity, it only utilises 
                 * the requested quantity at the purchased price.
                 */
                $requestedProductValue += $requestedQuantity * $stock['Unit Price'];
            } else if ($requestedQuantity > $stock['Quantity']) {
                /**
                 * if requested quantity is greated than the purchased quantity, which means whole purchase 
                 * order was utilised.
                 */
                $requestedProductValue += $stock['Quantity'] * $stock['Unit Price'];
            }
            
            //we substract the calculated quantity with the requested quantity.
            $requestedQuantity -= $stock['Quantity'];
        }

        $response = [
            'success' => true, 
            'data' => ['productValue' => round($requestedProductValue, 2)], 
            'message' => 'Success'
        ];

        return response($response, 200);
    }
}
