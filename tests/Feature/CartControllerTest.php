<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\CustomerOrder;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_adds_product_to_cart()
    {
        $product = Product::factory()->create(['price' => 100]);
        $userEmail = 'customer@example.com';

        $response = $this->postJson('api/cart/add_item', [
            'customer_email' => $userEmail,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', ['customer_email' => $userEmail]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function it_adds_product_to_existing_cart()
    {
        $product = Product::factory()->create(['price' => 100]);
        $userEmail = 'customer@example.com';

        $cart = Cart::create(['customer_email' => $userEmail]);
        $cart->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $cart->update(['total' => $product->price]);

        $response = $this->postJson('api/cart/add_item', [
            'customer_email' => $userEmail,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);
        $this->assertDatabaseHas('carts', [
            'customer_email' => $userEmail,
            'total' => $product->price * 3,
        ]);
    }

    /** @test */
    public function it_handles_non_existent_product()
    {
        $userEmail = 'customer@example.com';

        $response = $this->postJson('api/cart/add_item', [
            'customer_email' => $userEmail,
            'product_id' => 999,
            'quantity' => 1
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_checks_out_cart()
    {
        Mail::fake();

        $product = Product::factory()->create();
        $userEmail = 'customer@example.com';

        $cart = Cart::create(['customer_email' => $userEmail]);
        $cart->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $cart->update(['total' => $product->price * 3]);

        $response = $this->postJson('api/cart/checked_out', [
            'customer_email' => $userEmail,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', [
            'customer_email' => $userEmail,
            'is_checked_out' => 1,
        ]);
        $this->assertDatabaseHas('orders', [
            'cart_id' => $cart->id,
            'total_price' => $product->price * 3,
        ]);

        Mail::assertQueued(CustomerOrder::class, function ($mail) use ($userEmail) {
            return $mail->hasTo($userEmail);
        });
    }

    /** @test */
    public function it_handles_no_active_cart_when_checking_out()
    {
        $userEmail = 'customer@example.com';

        $response = $this->postJson('api/cart/checked_out', [
            'customer_email' => $userEmail,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => "$userEmail dont have active cart",
        ]);
    }

    /** @test */
    public function it_handles_already_checked_out_cart()
    {
        $product = Product::factory()->create(['price' => 100]);
        $userEmail = 'customer@example.com';

        $cart = Cart::create(['customer_email' => $userEmail, 'is_checked_out' => true]);
        $cart->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $cart->update(['total' => $product->price]);

        $response = $this->postJson('api/cart/checked_out', [
            'customer_email' => $userEmail,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => "$userEmail dont have active cart",
        ]);
    }

    /** @test */
    public function it_handles_adding_zero_or_negative_quantity()
    {
        $product = Product::factory()->create(['price' => 100]);
        $userEmail = 'customer@example.com';

        $response = $this->postJson('api/cart/add_item', [
            'customer_email' => $userEmail,
            'product_id' => $product->id,
            'quantity' => 0
        ]);

        $response->assertStatus(422);

        $response = $this->postJson('api/cart/add_item', [
            'customer_email' => $userEmail,
            'product_id' => $product->id,
            'quantity' => -1
        ]);

        $response->assertStatus(422);
    }
}
