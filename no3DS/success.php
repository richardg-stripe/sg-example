<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

$card_details = [
  'number' => "4242424242424242",
  'exp_month' => "02",
  'exp_year' => "22",
  'cvc' => "123"
];

$payment_intent = $stripe->paymentIntents->create([
  'amount' => 4740,
  'currency' => 'USD',
  'confirm' => true,
  'capture_method' => "manual",
  'payment_method_types' => ['card'],
  'payment_method_data' => [
    'type' => "card",
    'card' => $card_details
  ]
]);

echo $payment_intent;

$payment_intent = $stripe->paymentIntents->capture($payment_intent->id);

echo $payment_intent;


echo "Next month's payment!";

$payment_intent_2 = $stripe->paymentIntents->create([
  'amount' => 4740,
  'currency' => 'USD',
  'confirm' => true,
  'capture_method' => "manual",
  'payment_method_types' => ['card'],
  'payment_method_data' => [
    'type' => "card",
    'card' => $card_details
  ]
]);

$payment_intent_2 = $stripe->paymentIntents->capture($payment_intent_2->id);

echo $payment_intent_2;
