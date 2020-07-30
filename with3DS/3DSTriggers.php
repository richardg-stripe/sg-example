<?php
require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

// Create the logger
$logger = new Logger('my_logger');
// Now add some handlers
$logger->pushHandler(new StreamHandler(__DIR__.'/my_app.log', Logger::DEBUG));

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$stripe = new \Stripe\StripeClient($_ENV['STRIPE_API_KEY']);

$app = new \Slim\App;


$logger->info('My logger is now ready');

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
  echo("\n\nPlease redirect your browser to: $url\n\n");
}
