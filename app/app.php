<?php
require 'vendor/autoload.php';
require 'db/dbResolver.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Slim\Factory\AppFactory;
use db\dbResolver;

$app = AppFactory::create();

/**
 * Checkout Simulation
 */
$app->post('/checkout', function ($request, $response) {
    $client = new Client();
    $data   = $request->getParsedBody();
    $amount = $data['amount'] ?? null; /* Assuming front-end amount validation before checkout (check if empty or not a valid amount) */
    $email  = $data['email'] ?? null; /* Assuming emails are validated on the front-end before checkout (valid email, email not empty) */
    $commerceAPIURL = 'http://crypto/payment-api'; /* Simulated locally via /payment-api route */

    $response->withHeader('Content-Type', 'application/json');

    if ($amount && $email) {
        $paymentUrl = $client->get($commerceAPIURL)->getBody()->getContents();

        $response->withStatus(200)
                 ->getBody()
                 ->write('Your payment URL is: '.$paymentUrl);
    } else {
        $response->withStatus(400)
                 ->getBody()
                 ->write('There was a problem generating your payment url, please try again.');
    }

    return $response;
});

/**
 * Webhook Simulation
 */
$app->post('/webhook', function ($request, $response) {
    $dataPayload = $request->getParsedBody();
    $dbResolver  = new dbResolver();

    if (! isset($dataPayload['type']) || $dataPayload['type'] !== 'charge:confirmed') {
        return $response->withStatus(400)
                        ->getBody()
                        ->write('Transaction not confirmed.');
    }

    /**
     * Save/Log Transaction to Database
     */
    $transaction = [
        $dataPayload['customer_email'],
        $dataPayload['id'],
        $dataPayload['status'],
        date('c', strtotime('now')),
    ];

    $confirmTransaction = false;
    $duplicateTransaction = true;

    $tryCount = 0;
    $tryLimit = 5;

    while ($tryCount < $tryLimit && !$confirmTransaction && !$duplicateTransaction) {
        $confirmTransaction = $dbResolver->logTransaction($transaction);
        $duplicateTransaction = $dbResolver->checkDuplicateTransactionID($dataPayload['id']);

        if (! $confirmTransaction && ! $duplicateTransaction) {
            $tryCount++;
            sleep(3);
        }
    }

    $response->withHeader('Content-Type', 'application/json');

    if ($confirmTransaction) {
        $response->withStatus(200)
                 ->getBody()
                 ->write('Transaction has been confirmed.');
    } else {
        $response->withStatus(400)
                 ->getBody()
                 ->write('There was a problem with the confirmation, please try again.');
    }

    return $response;
});

/**
 * Simulates the Coinbase Commerce API
 */
$app->get('/payment-api', function ($request, $response) {
    $response->withHeader('Content-Type', 'application/json');
    $response->withStatus(200)
        ->getBody()
        ->write('https://fake.coinbase.com/pay/'.bin2hex(random_bytes(15)));

    return $response;
});

$app->get('/health', function ($request, $response) {
    return $response->withJson(['status' => 'OK']);
});


try {
    $app->run();
} catch (Throwable $e) {
    return $e->getMessage();
}