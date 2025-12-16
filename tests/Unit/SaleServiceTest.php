<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;
    private User $user;
    private Customer $customer;
    private Product $product;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = new SaleService();

        // Create test user
        $this->user = User::factory()->create();
        Auth::login($this->user);

        // Create test category
        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'status' => 'active',
        ]);

        // Create test customer
        $this->customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'is_active' => true,
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'quantity' => 10,
            'price' => 100.00,
            'cost_price' => 50.00,
            'status' => 'active',
        ]);
    }

    /**
     * Test that stock decreases after creating a sale.
     */
    public function test_stock_decreases_after_sale(): void
    {
        $initialStock = $this->product->quantity;
        $saleQuantity = 3;

        $sale = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => $saleQuantity,
            'unit_price' => $this->product->price,
            'total' => $this->product->price * $saleQuantity,
            'sale_date' => now()->toDateString(),
        ]);

        $this->product->refresh();

        $this->assertEquals($initialStock - $saleQuantity, $this->product->quantity);
        $this->assertNotNull($sale);
        $this->assertEquals($this->customer->id, $sale->customer_id);
    }

    /**
     * Test that sale creation throws exception when stock is insufficient.
     */
    public function test_sale_creation_fails_with_insufficient_stock(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuficiente');

        $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 100, // More than available stock
            'unit_price' => $this->product->price,
            'total' => $this->product->price * 100,
            'sale_date' => now()->toDateString(),
        ]);
    }

    /**
     * Test that stock is restored when sale is deleted.
     */
    public function test_stock_restored_when_sale_deleted(): void
    {
        $initialStock = $this->product->quantity;
        $saleQuantity = 2;

        // Create sale
        $sale = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => $saleQuantity,
            'unit_price' => $this->product->price,
            'total' => $this->product->price * $saleQuantity,
            'sale_date' => now()->toDateString(),
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - $saleQuantity, $this->product->quantity);

        // Delete sale
        $this->saleService->deleteSale($sale);

        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->quantity);
    }

    /**
     * Test that stock is restored and updated correctly when sale is updated.
     */
    public function test_stock_updated_correctly_when_sale_updated(): void
    {
        $initialStock = $this->product->quantity;
        $initialSaleQuantity = 2;
        $newSaleQuantity = 4;

        // Create sale
        $sale = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => $initialSaleQuantity,
            'unit_price' => $this->product->price,
            'total' => $this->product->price * $initialSaleQuantity,
            'sale_date' => now()->toDateString(),
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - $initialSaleQuantity, $this->product->quantity);

        // Create another product for update
        $newProduct = Product::factory()->create([
            'name' => 'New Product',
            'sku' => 'TEST-002',
            'category_id' => $this->category->id,
            'quantity' => 10,
            'price' => 150.00,
            'status' => 'active',
        ]);

        // Update sale with new product and quantity
        $this->saleService->updateSale($sale, [
            'customer_id' => $this->customer->id,
            'product_id' => $newProduct->id,
            'quantity' => $newSaleQuantity,
            'unit_price' => $newProduct->price,
            'total' => $newProduct->price * $newSaleQuantity,
            'sale_date' => now()->toDateString(),
        ]);

        // Original product should have stock restored
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->quantity);

        // New product should have stock decreased
        $newProduct->refresh();
        $this->assertEquals(10 - $newSaleQuantity, $newProduct->quantity);
    }

    /**
     * Test that invoice number is generated correctly.
     */
    public function test_invoice_number_generation(): void
    {
        $sale = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => $this->product->price,
            'total' => $this->product->price,
            'sale_date' => now()->toDateString(),
        ]);

        $this->assertStringStartsWith('INV', $sale->invoice_number);
        $this->assertEquals(11, strlen($sale->invoice_number)); // INV + YYYY + MM + 4 digits
    }

    /**
     * Test that sale items are created correctly.
     */
    public function test_sale_items_created_correctly(): void
    {
        $sale = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => $this->product->price,
            'total' => $this->product->price * 2,
            'sale_date' => now()->toDateString(),
        ]);

        $this->assertCount(1, $sale->saleItems);
        $this->assertEquals($this->product->id, $sale->saleItems->first()->product_id);
        $this->assertEquals(2, $sale->saleItems->first()->quantity);
        $this->assertEquals($this->product->price, $sale->saleItems->first()->unit_price);
    }
}
