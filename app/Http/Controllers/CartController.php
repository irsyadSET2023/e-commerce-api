<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProductToCartRequest;
use App\Http\Requests\CheckedOutCartRequest;
use App\Mail\CustomerOrder;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    //
    public function addProductToCart(AddProductToCartRequest $request)
    {
        $userEmail = $request->customer_email;
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;

        $menuPrice = Product::find($productId)->select(['price'])->first()->price;

        $totalPrice = $menuPrice * $quantity;

        $cart = Cart::where('customer_email', $userEmail)->where('is_checked_out', false)->first();


        if (!$cart) {
            DB::beginTransaction();
            $cart = Cart::create(
                ["customer_email" => $userEmail]
            );
            $cart->cartItems()->create(
                [
                    "cart_id" => $cart->id,
                    "product_id" => $productId,
                    "quantity" => $quantity
                ]
            );

            $cart->update(
                [
                    "total" => $totalPrice
                ]
            );
            DB::commit();
            return $this->sendResponse("Items add", null, 200);
        }

        $cartItems = $cart->cartItems;
        $previousPrice = $cart->total;

        if ($cartItems->where('product_id', $productId)->where('cart_id', $cart->id)->first()) {
            DB::beginTransaction();
            $previousQuantity = $cartItems->where('product_id', $productId)->first()->quantity;
            $cart->cartItems->where('product_id', $productId)->first()->update(
                [
                    "quantity" => $previousQuantity + $quantity
                ]
            );


            $cart->update(
                [
                    "total" => $totalPrice + $previousPrice
                ]
            );

            DB::commit();
        } else {
            DB::beginTransaction();

            $cart->cartItems()->create(
                [
                    "cart_id" => $cart->id,
                    "product_id" => $productId,
                    "quantity" => $quantity
                ]
            );


            $cart->update(
                [
                    "total" => $totalPrice + $previousPrice
                ]
            );

            DB::commit();
        }

        return $this->sendResponse("Items add", null, 200);
    }


    public function checkedOutCart(CheckedOutCartRequest $request)
    {
        $customerEmail = $request->customer_email;

        $activeCart = Cart::where('customer_email', $customerEmail)
            ->where('is_checked_out', false)
            ->with(['cartItems.product'])
            ->first();

        if (!$activeCart) {
            return $this->sendError("$customerEmail dont have active cart", null, 400);
        }
        DB::beginTransaction();

        $activeCart->update(
            [
                "is_checked_out" => 1
            ]
        );
        Order::create(
            [
                "total_price" => $activeCart->total,
                "cart_id" => $activeCart->id,
                "discount_price" => 0,
            ]
        );
        DB::commit();

        $activeCartItems = $activeCart->cartItems;

        $activerCartItemsInvoice = $activeCartItems->map(function ($activeCartItem) {
            return [
                "product_id" => $activeCartItem->product->id,
                "product_name" => $activeCartItem->product->name,
                "quantity" => $activeCartItem->quantity
            ];
        });

        Log::info("Cart Items", $activeCartItems->toArray());

        Mail::to($customerEmail)->send(new CustomerOrder($activerCartItemsInvoice->toArray()));

        return $this->sendResponse("Cart checked out", null, 200);
    }
}
