<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$stripe = new \Stripe\StripeClient($_ENV['STRIPE_API_KEY']);

$card_details = [
  'number' => "4000002500003155",
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
  ],
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
