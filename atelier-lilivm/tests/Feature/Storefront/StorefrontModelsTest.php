<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

test('a category has many products and a product belongs to a category', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    expect($category->products)->toHaveCount(1)
        ->and($category->products->first()->is($product))->toBeTrue()
        ->and($product->category->is($category))->toBeTrue();
});

test('a user has one cart and many orders', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->for($user)->create();
    $order = Order::factory()->for($user)->create();

    expect($user->cart->is($cart))->toBeTrue()
        ->and($user->orders->first()->is($order))->toBeTrue()
        ->and($cart->user->is($user))->toBeTrue()
        ->and($order->user->is($user))->toBeTrue();
});

test('a cart exposes its items, products, and total', function () {
    $product = Product::factory()->create(['price' => 25.50]);
    $cart = Cart::factory()->create();
    $item = CartItem::factory()->for($cart)->for($product)->create(['quantity' => 3]);

    expect($item->cart->is($cart))->toBeTrue()
        ->and($item->product->is($product))->toBeTrue()
        ->and($item->subtotal)->toBe(76.5)
        ->and($cart->items)->toHaveCount(1)
        ->and($cart->products->first()->is($product))->toBeTrue()
        ->and($cart->total)->toBe(76.5);
});

test('an order exposes its items and snapshot data', function () {
    $order = Order::factory()->create();
    $product = Product::factory()->create();
    $item = OrderItem::factory()->for($order)->for($product)->create([
        'unit_price' => 10.00,
        'quantity' => 2,
        'subtotal' => 20.00,
    ]);

    expect($item->order->is($order))->toBeTrue()
        ->and($item->product->is($product))->toBeTrue()
        ->and($order->items->first()->is($item))->toBeTrue();
});

test('product scopes filter active and in-stock products', function () {
    $active = Product::factory()->create(['stock' => 10]);
    $inactive = Product::factory()->inactive()->create(['stock' => 5]);
    $outOfStock = Product::factory()->outOfStock()->create();

    expect(Product::query()->active()->pluck('id')->all())->toBe([$active->id, $outOfStock->id])
        ->and(Product::query()->inStock()->pluck('id')->all())->toBe([$active->id, $inactive->id])
        ->and($active->isInStock())->toBeTrue()
        ->and($outOfStock->isInStock())->toBeFalse();
});

test('models cast their attributes to application types', function () {
    $product = Product::factory()->create([
        'price' => 19.99,
        'stock' => 4,
        'is_active' => true,
    ]);

    expect($product->price)->toBe('19.99')
        ->and($product->stock)->toBe(4)
        ->and($product->is_active)->toBeTrue();
});

test('a user with the admin role is identified as an administrator', function () {
    expect(User::factory()->admin()->create()->isAdmin())->toBeTrue()
        ->and(User::factory()->create()->isAdmin())->toBeFalse();
});
