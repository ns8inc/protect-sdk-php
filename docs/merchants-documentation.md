# Merchants Documentation

## Table of Contents

- [Purpose of the Merchants Client](#purpose-of-the-merchants-client)
- [Example Merchants Client Usage](#example-merchants-client-usage)

## Purpose of the Merchants Client

The purpose of the Merchants class is to easily acquire information about the
current merchant. This is necessary in order to load the Protect client iframe.

## Example Merchants Client Usage

Fetching the access token for requests within the HTTP Client. By default, the
HTTP Client will automatically pull the access token and does not need to be set
individually for requests unless specifically told to do so.

```php
<?php
declare(strict_types=1);
use NS8\ProtectSDK\Merchants\Client as MerchantsClient;
$merchantsClient = new MerchantsClient();
$merchant = $merchantsClient->getCurrent();
if (empty($merchant->error)) {
  echo "Success: $merchant->name\n";
} else {
  echo "Error: $merchant->statusCode $merchant->error\n";
}
```
