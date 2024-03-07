<?php

namespace Unetway\Paypal;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class Paypal
{

    /**
     * @var Client $client
     */
    private Client $client;

    /**
     * @var string
     */
    private string $baseUri = 'https://api.paypal.com/v1/';

    /**
     * @var string
     */
    private string $sandboxUri = 'https://api.sandbox.paypal.com/v1/';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var bool
     */
    private $sandbox = false;

    /**
     * @var string
     */
    private string $paymentLink;

    public function __construct(array $params)
    {
        if (isset($params['sandbox'])) {
            $this->sandbox = $params['sandbox'];
        }

        if (empty($params['client_id'])) {
            throw new Exception('Params client_id is not defined');
        }

        if (empty($params['secret'])) {
            throw new Exception('Params secret is not defined');
        }

        $this->clientId = $params['client_id'];
        $this->secret = $params['secret'];

        $this->client = new Client([
            'base_uri' => $this->sandbox ? $this->sandboxUri : $this->baseUri,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ],
        ]);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getAccessToken(): string
    {
        try {
            $client = new Client();
            $url = $this->baseUri . 'oauth2/token';

            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US',
                ],
                'auth' => [$this->clientId, $this->secret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
            ]);

            $content = $response->getBody()->getContents();
            $response = json_decode($content, true);

            return $response['access_token'];
        } catch (ClientException $exception) {
            throw new Exception($exception);
        }
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function createProduct(array $params): array
    {
        return $this->request('catalogs/products', 'POST', [
            'json' => $params,
            'headers' => [
                'PayPal-Request-Id' => $this->getUuid(),
            ],
        ]);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $params
     * @return array
     * @throws Exception
     */
    private function request(string $url, string $method, array $params = []): array
    {
        try {
            $response = $this->client->request($method, $url, $params);
            $content = $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            throw new Exception($exception);
        }

        return json_decode($content, true);
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return uniqid('', true);
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function createBillingPlan(array $params): array
    {
        return $this->request('billing/plans', 'POST', [
            'json' => $params,
        ]);
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function createSubscriptions(array $params): array
    {
        $response = $this->request('billing/subscriptions', 'POST', [
            'json' => $params,
        ]);

        if ($response && isset($response['links'][0]['href'])) {
            $this->paymentLink = $response['links'][0]['href'];
        }

        return $response;
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function activateSubscriptions(string $id): array
    {
        return $this->request("billing/subscriptions/{$id}/activate", 'POST', [
            'json' => [
                'reason' => 'Reactivating on customer request',
            ],
        ]);
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function suspendSubscriptions(string $id): array
    {
        return $this->request("billing/subscriptions/{$id}/suspend", 'POST', [
            'json' => [
                'reason' => 'Customer-requested pause',
            ],
        ]);
    }

    /**
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function cancelSubscriptions(string $id): array
    {
        return $this->request("billing/subscriptions/{$id}/cancel", 'POST', [
            'json' => [
                'reason' => 'Not satisfied with the service',
            ],
        ]);
    }

    /**
     * @param string $id
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function reviseSubscriptions(string $id, array $params): array
    {
        return $this->request("billing/subscriptions/{$id}/revise", 'POST', [
            'json' => $params,
        ]);
    }

    /**
     * @param string $id
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function updatePricePlan(string $id, array $params): array
    {
        return $this->request("billing/plans/{$id}/update-pricing-schemes", 'POST', [
            'json' => $params,
        ]);
    }

}