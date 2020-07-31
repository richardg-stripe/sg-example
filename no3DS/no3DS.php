<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

\Stripe\Stripe::setMaxNetworkRetries(3);
$stripe = new \Stripe\StripeClient($_ENV['STRIPE_API_KEY']);

$payment_method = $stripe->paymentMethods->create([
  'type' => "card",
  'card' => [
    'number' => "4242424242424242",
    'exp_month' => "02",
    'exp_year' => "22",
    'cvc' => "123"
  ]
]);

$customer =$stripe->customers->create([
  'name' => 'Dave Dave',
  'payment_method' => $payment_method->id
]);

$payment_intent = $stripe->paymentIntents->create([
  'amount' => 4740,
  'currency' => 'USD',
  'customer' => $customer->id,
  'payment_method' => $payment_method->id,
  'confirm' => true,
  'capture_method' => "manual",
  'payment_method_types' => ['card'],
]);

echo "Created payment intent\n";

$payment_intent = $stripe->paymentIntents->capture($payment_intent->id);

echo "Captured payment intent\n";

$saved_payment_method = $stripe->paymentMethods->all([
  'customer' => $customer->id,
  'type' => 'card',
])->first();

$payment_intent_2 = $stripe->paymentIntents->create([
  'amount' => 4740,
  'currency' => 'USD',
  'customer' => $customer->id,
  'payment_method' => $saved_payment_method->id,
  'confirm' => true,
  'capture_method' => "manual",
  'payment_method_types' => ['card'],
]);

echo "Created 2nd payment intent\n";

$payment_intent_2 = $stripe->paymentIntents->capture($payment_intent_2->id);

echo "Captured 2nd payment intent\n";
