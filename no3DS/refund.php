<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

\Stripe\Stripe::setMaxNetworkRetries(3); // This will use idempotency keys for you
$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

$payment_intent = $stripe->paymentIntents->create([
  'amount' => 4740,
  'currency' => 'USD',
  'confirm' => true,
  'capture_method' => "manual",
  'payment_method_types' => ['card'],
  'payment_method_data' => [
    'type' => "card",
    'card' => [
      'number' => "4242424242424242",
      'exp_month' => "02",
      'exp_year' => "22",
      'cvc' => "123"
    ]
  ]
]);

echo $payment_intent;

$payment_intent = $stripe->paymentIntents->capture($payment_intent->id);

$stripe->refunds->create([
  'payment_intent' => $payment_intent->id,
  'amount' => 4000 // Partial refund, optional
]);
