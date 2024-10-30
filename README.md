# Deano_AdyenWhitelistApi
## Overview
A Magento 2 module that allows adyen URL management via the Adyen API - https://docs.adyen.com/api-explorer/Management/3/get/merchants/(merchantId)/apiCredentials/(apiCredentialId)/allowedOrigins

## Installation
```
composer require deano/module-adyen-whitelist-api
```

## Configuration
Before you start using this module ensure that you have the following configuration set under Stores > Configuration > Sales > Payment Methods > Adyen > Configure > Required Settings
- Credential ID
- Test API Key
- Test Client Key
- Merchant Account
- Adyen Api Type (Whether calls from this module should use merchant or company. You can find out which you need to use in the Adyen Admin by clicking the Adyen symbol in top left, and next to your account should be company or merchant.)

## Usage
Remove all URLs from each store.
```
bin/magento adyen:whitelist:urls --mode remove
```

Add all URLs from each store.
```
bin/magento adyen:whitelist:urls --mode add
```

List out all the URLs currently whitelisted.
```
bin/magento adyen:whitelist:urls --mode list
```
