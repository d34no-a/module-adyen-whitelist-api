<?php

/**
 * Copyright © Alice Dean. All rights reserved.
 */

declare(strict_types=1);

namespace Deano\AdyenWhitelistApi\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const ADYEN_CREDENTIAL_ID_PATH = 'payment/adyen_group_all_in_one/adyen_required_settings/credential_id';
    private const ADYEN_TEST_API_KEY_PATH = 'payment/adyen_abstract/api_key_test';
    private const ADYEN_MERCHANT_ACCOUNT_PATH = 'payment/adyen_abstract/merchant_account';
    private const ADYEN_COMPANY_ACCOUNT_PATH = 'payment/adyen_group_all_in_one/adyen_required_settings/company_account';
    private const ADYEN_API_TYPE = 'payment/adyen_group_all_in_one/adyen_required_settings/adyen_api_type';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get Adyen Credential Id
     *
     * @param string|null $storeId
     *
     * @return null|string
     */
    public function getAdyenCredentialId(?string $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::ADYEN_CREDENTIAL_ID_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Adyen Credential Id
     *
     * @param string|null $storeId
     *
     * @return null|string
     */
    public function getAdyenTestApiKey(?string $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::ADYEN_TEST_API_KEY_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }


    /**
     * Get Adyen merchant account
     *
     * @param string|null $storeId
     *
     * @return null|string
     */
    public function getAdyenMerchantAccount(?string $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::ADYEN_MERCHANT_ACCOUNT_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Adyen company account
     *
     * @param string|null $storeId
     *
     * @return null|string
     */
    public function getAdyenCompanyAccount(?string $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::ADYEN_COMPANY_ACCOUNT_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Adyen API Type
     *
     * @param string|null $storeId
     *
     * @return null|string
     */
    public function getAdyenApiType(?string $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::ADYEN_API_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
