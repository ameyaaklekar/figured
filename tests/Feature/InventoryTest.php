<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Inventory;

class InventoryTest extends TestCase
{
    protected $testData = [
        [
            "Date" => "05/06/2020",
            "Type" => "Purchase",
            "Quantity" => "10",
            "Unit Price" => "5"
        ],
        [
            "Date" => "07/06/2020",
            "Type" => "Purchase",
            "Quantity" => "30",
            "Unit Price" => "4.5"
        ],
        [
            "Date" => "08/06/2020",
            "Type" => "Application",
            "Quantity" => "-20",
            "Unit Price" => ""
        ]
    ];

    public function testValidRequestedQuantity() 
    {
        $inventory = new Inventory();
        $response = $inventory->validateRequestedQuantity($this->testData, 10);
        $this->assertTrue($response);
    }

    public function testInValidRequestedQuantity() 
    {
        $inventory = new Inventory();
        $response = $inventory->validateRequestedQuantity($this->testData, 40);
        $this->assertFalse($response);
    }

    public function testAvailableStock() 
    {
        $inventory = new Inventory();
        $data = $inventory->getAvailableStock($this->testData);

        $this->assertEquals([
            [
                "Date" => "07/06/2020",
                "Type" => "Purchase",
                "Quantity" => "20",
                "Unit Price" => "4.5"
            ]
        ], $data);
    }

    public function testStockAmountCalculation()
    {
        $inventory = new Inventory();
        $data = $inventory->getAvailableStock($this->testData);
        $amount = $inventory->calculateStockAmount($data, 10);
        $this->assertEquals(45, $amount);
    }

    public function testApiEndpointForInvalidQuantity()
    {
        $response = $this->json('POST', 'api/product/value', ['quantity' => 'test']);
        $response->assertStatus(422);
    }

    public function testApiEndpointForZeroQuantity()
    {
        $response = $this->json('POST', 'api/product/value', ['quantity' => 0]);
        $response->assertStatus(422);
    }

    public function testApiEndpointForValidQuantity()
    {
        $response = $this->json('POST', 'api/product/value', ['quantity' => 20]);
        $response->assertStatus(200);
    }

    public function testApiEndpointForQuantityIsGreaterThanAvailableQuantity()
    {
        $response = $this->json('POST', 'api/product/value', ['quantity' => 50]);
        $response->assertStatus(400)
            ->assertJsonPath('message', 'Requested quantity exceeds the total available quantity');
    }
}
