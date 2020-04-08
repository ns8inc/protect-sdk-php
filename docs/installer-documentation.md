# Installer Documentation

## Table of Contents

- [Purpose of the Queue Client](#purpose-of-the-queue-client)
- [Example of a Queue Client Implementation](#example-of-a-queue-client-implementation)

## Purpose of the Queue Client

The purpose of the Installer Client is to provide easy access with initiating
contact with the NS8 Protect API through allowing registration via the
`install` method which returns an array containing an access token needed for
making requests.

## Example of a Queue Client Implementation

The following serve as examples of implementation of the
Installl Client to demonstrate intended uses:

```php
<?php
declare(strict_types=1);
use NS8\ProtectSDK\Install\Client as InstallClient;
$platform = 'magento'; // A valid platform type must be provided as a string
$installData = [
            'email' => '123@test.com', // email is a mandatory attribute
            'storeUrl' => 'https://example.com', // storeUrl is a mandatory attribute
            'firstName' => 'Test', // firstName is an optional attribute
            'lastName' => 'User', // lastName is an optional attribute
            'phone' => '(111) 222-3333' // phone is an optional attribute
;
$installResult = InstallClient::install($platform, $installData);
$accessToken = $installResult['accessToken'];
Platform::save('ns8/access_token', $accessToken);
```
