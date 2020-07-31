<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

\Stripe\Stripe::setMaxNetworkRetries(3);
$stripe = new \Stripe\StripeClient($_ENV['STRIPE_API_KEY']);

$payment_method = $stripe->paymentMethods->create([
  'type' => "card",
  'card' => [
    // https://stripe.com/docs/testing#regulatory-cards
    'number' => "4000002500003155", // Card triggers 3DS
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
  'setup_future_usage' => "off_session",
  'return_url' => 'https://sg.com/paymentComplete'
]);

echo $payment_intent;

if ($payment_intent->next_action->type =="redirect_to_url") {
  $url = $payment_intent->next_action->redirect_to_url->url;
  echo("\n\nPlease redirect customer's browser to: $url\n\n");
} else {
  echo("No need to redirect customer's browser");
}
