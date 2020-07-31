<?php
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

require './config.php';

if (PHP_SAPI == 'cli-server') {
  $_SERVER['SCRIPT_NAME'] = '/index.php';
}

$app = new \Slim\App;

// Instantiate the logger as a dependency
$container = $app->getContainer();
$container['logger'] = function ($c) {
  $logger = new Monolog\Logger('Api logger');
  $logger->pushHandler(new Monolog\Handler\StreamHandler('./app.log', \Monolog\Logger::DEBUG));
  return $logger;
};

$app->post('/webhook', function(Request $request, Response $response) {
  $logger = $this->get('logger');
  $logger->info("Webhook started");
  $stripe = new \Stripe\StripeClient($_ENV['STRIPE_API_KEY']);
  $event = $request->getParsedBody();
  $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'];
  try {
    // https://stripe.com/docs/webhooks/signatures
    $event = \Stripe\Webhook::constructEvent(
      $request->getBody(),
      $request->getHeaderLine('stripe-signature'),
      $webhookSecret
    );
  } catch (\Exception $e) {
    $error_message = $e->getMessage();
    $logger->info("Error: $error_message");
    return $response->withJson([ 'error' => $error_message ])->withStatus(403);
  }

  $type = $event['type'];
  $object = $event['data']['object'];

  $logger->info("event type: $type");

  if ($type == 'payment_intent.amount_capturable_updated') {
    $logger->info($object);
    $payment_intent_id = $object['id'];
    $logger->info($payment_intent_id);

    $payment_intent = $stripe->paymentIntents->capture($payment_intent_id);



    $logger->info("Time for next month's payment!");
    $customer_id = $payment_intent->customer;
    $logger->info($customer_id);

    $saved_payment_method = $stripe->paymentMethods->all([
      'customer' => $customer_id,
      'type' => 'card',
    ])->first();
    $payment_intent_2 = $stripe->paymentIntents->create([
      'amount' => 4740,
      'currency' => 'USD',
      'confirm' => true,
      'off_session' => true,
      'payment_method_types' => ['card'],
      'payment_method' => $saved_payment_method->id,
      'customer' => $customer_id
    ]);

  } else if ($type == 'payment_intent.succeeded') {
    // Fulfill any orders, e-mail receipts, etc
    // To cancel the payment you will need to issue a Refund (https://stripe.com/docs/api/refunds)
    $logger->info('💰 Payment received! ');
  } else if ($type == 'payment_intent.payment_failed') {
    $logger->info('❌ Payment failed.');
  }

  return $response->withJson([ 'status' => 'success' ])->withStatus(200);
});

$app->run();
