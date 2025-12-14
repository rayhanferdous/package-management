<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;

class StripeService
{
    public function createCheckoutSession(Package $package, User $user, Purchase $purchase)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $currentPrice = $package->current_price;

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $package->name,
                        'description' => substr($package->description, 0, 200),
                        'images' => $package->images->isNotEmpty() 
                            ? [url('storage/' . $package->images->first()->image_path)]
                            : [],
                    ],
                    'unit_amount' => $currentPrice->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.cancel') . '?session_id={CHECKOUT_SESSION_ID}',
            'customer_email' => $user->email,
            'client_reference_id' => $purchase->id,
            'metadata' => [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'purchase_id' => $purchase->id,
                'price_type' => $currentPrice->price_type,
            ],
        ]);

        return $session;
    }

    public function retrieveSession($sessionId)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        return \Stripe\Checkout\Session::retrieve($sessionId);
    }

    public function retrievePaymentIntent($paymentIntentId)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        return \Stripe\PaymentIntent::retrieve($paymentIntentId);
    }
}