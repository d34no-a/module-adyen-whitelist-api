<?php
/**
 * Copyright © Alice Dean. All rights reserved.
 */

declare(strict_types=1);

namespace Deano\AdyenWhitelistApi\Console\Command;

use Deano\AdyenWhitelistApi\Service\AdyenApi;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AdyenWhitelist extends Command
{
    private const MODE = 'mode';

    /**
     * @param UrlInterface $url
     * @param AdyenApi $adyenApi
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $name
     */
    public function __construct(
        private readonly UrlInterface $url,
        private readonly AdyenApi $adyenApi,
        private readonly Json $json,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('adyen:whitelist:urls');
        $this->setDescription((string) __('Update/List adyen whitelist URLs. Mode should be either \'add\', \'remove\', \'list\'.'));
        $this->addOption(
            self::MODE,
            null,
            InputOption::VALUE_REQUIRED,
            (string) __('Mode')
        );

        parent::configure();
    }

    /**
     * Query Adyen API based on mode supplied
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = $input->getOption(self::MODE);
        $whitelist = $this->getStores();

        if ($mode === 'add') {
            $this->processAdd($output, $whitelist);
        } elseif ($mode === 'remove') {
            $this->processRemove($output, $whitelist);
        } elseif ($mode === 'list') {
            $this->processList($output);
        } else {
            $output->writeln('<error>Please specify mode: \'add\', \'remove\', \'list\'.</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Add all store Urls
     *
     * @param OutputInterface $output
     * @param array $whitelist
     *
     * @return void
     */
    private function processAdd(OutputInterface $output, array $whitelist): void
    {
        foreach ($whitelist as $store) {
            $response = $this->queryAdyenApi(Request::HTTP_METHOD_POST, ['domain' => $store['url']], $store['id']);
            $output->writeln((string) $response['message']);
        }
    }

    /**
     * Remove all store Urls
     *
     * @param OutputInterface $output
     * @param array $whitelist
     *
     * @return void
     */
    private function processRemove(OutputInterface $output, array $whitelist): void
    {
        $whitelistUrls = array_column($whitelist, 'url');
        $response = $this->getWhitelistedUrls();

        if ($response['response']->getStatusCode() === 200) {
            $whitelistedUrls = $this->json->unserialize($response['response']->getBody()->getContents())['data'];

            $filteredUrls = array_filter($whitelistedUrls, function ($url) use ($whitelistUrls) {
                return isset($url['domain']) && in_array($url['domain'], $whitelistUrls, true);
            });

            foreach ($filteredUrls as $url) {
                $storeId = $whitelist[array_search($url['domain'], array_column($whitelist, 'url'))]['id'];
                $response = $this->queryAdyenApi(Request::HTTP_METHOD_DELETE, ['domain' => $url['domain']], (string)$storeId, $url['id']);
                $output->writeln((string) $response['message']);
            }
        } else {
            $output->writeln((string) $response['message']);
        }
    }

    /**
     * Output Whitelisted Urls
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    private function processList(OutputInterface $output): void
    {
        $response = $this->getWhitelistedUrls();
        $output->writeln((string) $response['message']);

        if ($response['response']->getStatusCode() === 200) {
            $whitelistedUrls = $this->json->unserialize($response['response']->getBody()->getContents());

            if ($whitelistedUrls) {
                foreach ($whitelistedUrls['data'] as $url) {
                    if (isset($url['domain'])) {
                        $output->writeln((string) $url['domain']);
                    }
                }
            } else {
                $output->writeln((string) __('Looks like there\'s no URLs whitelisted.'));
            }
        }
    }

    /**
     * Get White Listed Urls
     *
     * @return Response|array
     */
    private function getWhitelistedUrls(): Response|array
    {
        return $this->queryAdyenApi(Request::HTTP_METHOD_GET);
    }

    /**
     * Get Stores Data to Whitelist
     *
     * @return array
     */
    private function getStores(): array
    {
        $stores = [];

        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->isActive()) {
                $stores[] = [
                    'id' => (string) $store->getId(),
                    'url' => rtrim($store->getBaseUrl(), '/') //Adyen doesn't save URLs with trailing slashes
                ];
            }
        }

        return $stores;
    }

    /**
     * Query Adyen API and return message based on response status
     *
     * @param string $httpType
     * @param array $url
     * @param string|null $storeId
     * @param string|null $originId
     *
     * @return array
     */
    private function queryAdyenApi(string $httpType, array $url = [], string $storeId = null, string $originId = null): array
    {
        $response = $this->adyenApi->execute($httpType, $url, $storeId, $originId);
        $responseStatus = $response->getStatusCode();

        $messages = [
            200 => __('<info>200: The request has succeeded.</info>'),
            204 => __('<info>204: The request has been successfully processed, but there is no additional content.</info>'),
            400 => __('<error>400: A problem has occurred reading or understanding the request.</error>'),
            401 => __('<error>401: Authentication required. Check the details in the admin are correct.</error>'),
            403 => __('<error>403: Insufficient permissions to process the request.</error>'),
            422 => __('<error>422: A request validation error.</error>'),
            500 => __('<error>500: The server could not process the request.</error>'),
            'default' => __('<error>Uh oh, speak to a developer.</error>')
        ];

        return ['message' => $messages[$responseStatus] ?? $messages['default'], 'response' => $response];
    }
}
