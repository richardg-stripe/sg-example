<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

\Stripe\Stripe::setMaxNetworkRetries(3);
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
      // https://stripe.com/docs/testing#regulatory-cards
      'number' => "4000002500003155", // Card triggers 3DS
      'exp_month' => "02",
      'exp_year' => "22",
      'cvc' => "123"
    ]
  ]
]);

echo $payment_intent;

if ($payment_intent->status == 'requires_action') {
  echo "3DS was triggered, we cannot capture";
}
// Throws exception
$payment_intent = $stripe->paymentIntents->capture($payment_intent->id);

