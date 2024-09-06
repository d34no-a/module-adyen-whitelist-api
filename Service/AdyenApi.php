<?php

/**
 * Copyright © Alice Dean. All rights reserved.
 */

declare(strict_types=1);

namespace Deano\AdyenWhitelistApi\Service;

use Deano\AdyenWhitelistApi\Scope\Config;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AdyenApi
{
    /** @var API request URL string  */
    const API_REQUEST_URI = 'https://management-test.adyen.com/';

    /** @var API request endpoint string  */
    const API_REQUEST_ENDPOINT = 'v3/merchants/%s/apiCredentials/%s/allowedOrigins';

    /**
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Config $config
     * @param EncryptorInterface $encryptor
     * @param Json $json
     */
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly ResponseFactory $responseFactory,
        private readonly Config $config,
        private readonly EncryptorInterface $encryptor,
        private readonly Json $json,
    ) {
    }

    /**
     * Query Adyen API to Add/Remove/List whitelisted URLs
     *
     * @param string $requestMethod
     * @param array $params
     * @param string|null  $storeId
     * @param string|null $originId
     *
     * @return Response
     */
    public function execute(
        string $requestMethod,
        array $params,
        ?string $storeId,
        ?string $originId
    ): Response {
        $endpoint = self::API_REQUEST_URI . sprintf(
            self::API_REQUEST_ENDPOINT,
            $this->config->getAdyenMerchantAccount($storeId),
            $this->config->getAdyenCredentialId($storeId)
        );

        if ($originId) {
            $endpoint .= '/' . $originId;
        }

        /** @var Client $client */
        $client = $this->clientFactory->create();
        $headers = [
            'X-API-Key' => $this->encryptor->decrypt($this->config->getAdyenTestApiKey($storeId)),
            'Content-Type' => 'application/json'
        ];
        $body = $this->json->serialize($params);

        try {
            return $client->request($requestMethod, $endpoint, ['headers' => $headers, 'body' => $body]);
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }
}
