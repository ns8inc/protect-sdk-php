## Security Documentation

### Purpose of the Security Client
The purpose of the Security class is to manage credentials and processes related to authenticating with NS8 services and ensuring required requests contain the appropriate access token. In its initial form, the security client is expected to manage the access token value needed for NS8 requests and provide a structure for future authentication/security purposes. Future cases include:
  * OAuth integration for standardized authentication
  * Client-side access aids so developers can more easily integrate NS8 Events into the system
  * General encryption/decryption of protected information.


### Example Security Client Usages

Fetching the access token for requests
```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Security\Client as SecurityClient;

$accessToken = SecurityClient::getNs8AccessToken();
$httpClient = new HttpClient();
$httpClient->setAccessToken($accessToken);

$httpClient->post('/switch/executor', [], 'action' => 'CREATE_ORDER_ACTION');
```

Setting the access token for requests
```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Http\Client as HttpClient;
use NS8\ProtectSDK\Security\Client as SecurityClient;

// Override the access token set in JSON or set it at run-time
$accessToken = SecurityClient::setNs8AccessToken('ACCESS_TOKEN_VALUE_HERE');

// Later in request logic
$httpClient = new HttpClient();
$accessToken = SecurityClient::getNs8AccessToken();
$httpClient->setAccessToken($accessToken);

$httpClient->post('/switch/executor', [], 'action' => 'CREATE_ORDER_ACTION');
```
