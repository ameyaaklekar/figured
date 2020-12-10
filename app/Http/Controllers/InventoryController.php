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
    public $inventoryData;

    /**
     * Accepts "product/value" request. Computes the available stock and
     * returns the value for the requested quantity
     *
     * @param Request $request
     * @return void
     */
    public function getProductValue(Request $request) {
        $validationRule = [
            'quantity' => ['required', 'integer', 'min:1'] 
        ];

        $messages = [
            'required' => 'The :attribute is required.',
            'integer' => 'Please enter valid quantity'
        ];

        // Request Validation
        Validator::make($request->all(), $validationRule, $messages)->validate();

        $requestedQuantity = (int) $request->quantity;

        //Gets the inventory data from the datasource
        $inventoryData = Inventory::getInventoryData();
        
        //Gets the available stock from all the purchases and application based on the dates
        $availableStock = Inventory::getAvailableStock($inventoryData);

        //setting the initial requested value to zero.
        $requestedProductValue = 0;
        
        // In case there is not data in the datasource or database 
        if (empty($availableStock)) return response(['success' => true, 'data' => [], 'message' => 'No Stock Available'], 200);

        if (!$this->validateRequestedQuantity($availableStock, $requestedQuantity)) 
            return response(['success' => false, 'message' => 'Requested quantity exceeds the total available quantity'], 400);
        
        $requestedProductValue = Inventory::calculateStockAmount($availableStock, $requestedQuantity);

        $response = [
            'success' => true, 
            'data' => ['productValue' => $requestedProductValue], 
            'message' => 'Success'
        ];

        return response($response, 200);
    }

    /**
     * To verify the requested quantity is available
     *
     * @param Array $availableStock
     * @param Integer $requestedQuantity
     * @return void
     */
    public function validateRequestedQuantity($availableStock, $requestedQuantity) {
        /**
         * we are getting the total stock quantity available regardless the purchase date,
         * to verify if the requested quantity is available.
         */
        $totalAvailableQuantity = array_reduce(array_column($availableStock, 'Quantity'), 
            function ($totalQuantity, $stock) {
                return $totalQuantity += $stock;
            }, 0);

        // if requested quantity is greater than the total available quantity, we retun an 400 response.
        if ($requestedQuantity > $totalAvailableQuantity) return false;

        return true;
    }
}
