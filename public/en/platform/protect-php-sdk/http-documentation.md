## HTTP Documentation

### Purpose of the HTTP Client
The purpose of the HTTP client is to provide a clean, functional class that developers can instantiate to make API request calls to NS8's services. The class should serve as a smooth "in-between" layer for the SDK's business logic implementations and HTTP requirements when interacting with the NS8 API. The core functions of the class are to:
  * Permit NS8 API calls with proper authorization set
  * Provide flexibility, generic request options for fetching and posting data to NS8's API
  * Provide basic sanitization and validation for calls being sent to NS8's API


### Example HTTP Client Implementations
The following serves as example of implentations of the HTTP Client. Please note that the HTTP client will handle the domain component of the URI so only the endpoint path needs to be specified.

```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Http\Client as HttpClient;

$httpClient = new HttpClient('Auth Username', 'Access Token');
$httpClient->get('endpoint/get', ['param_1' => 'value_2', 'param_2' => 'value_2']);

// Send a GET (request type can be variable for this method) request specifically intended for Non-JSON responses such as an analytics script.
$httpClient->sendNonObjectRequest('endpoint/nonjson', 'GET', ['param_1' => 'value_2', 'param_2' => 'value_2']);

$httpClient->post('endpoint/post', ['new_record_key_1' => 'data_key_1', 'new_record_key_2' => 'data_key_2']);

$httpClient->put('endpoint/put', ['existing_record_key_1' => 'data_key_1', 'existing_record_key_2' => 'data_key_2']);

$httpClient->delete('endpoint/delete', ['record_id' => 'sample_id_value']);
```

The HTTP client also supports custom HTTP clients based off of the Zend Client when initializing the client object such as this:
```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Http\Client as HttpClient;
use Zend\Http\Client as ZendClient;
use Zend\Http\Client\Adapter\Test as ZendTestAdapter;

$adapter = new ZendTestAdapter();
$testHttpClient = new ZendClient('ns8.com', ['adapter' => $adapter]);

// The third argument "true" lets the NS8 HTTP client know to automatically set session data for HTTP requests
$httpClient = new Client('test', 'test', true, $testHttpClient);
```

The HTTP client permits setting/getting auth username, access token, and session data dynamically following object instantation as well.
```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Http\Client as HttpClient;

$httpClient = new HttpClient();

// Auth Username
$authUsername = 'test'
$client->setAuthUsername($authUsername);

// Later fetching auth username from client object
$authUsername = $client->getAuthUsername();

// Access Token
$accessToken = 'test_token';
$httpClient = new HttpClient();
$httpClient->setAccessToken($accessToken);

// Later fetching access token from client object
$accessToken = httpClient->getAccessToken();


// Session Data
$sessionData = [
  'acceptLanguage'    => 'en-US,en;q=0.5',
  'id'                => 'd533c19f-71d6-4372-a170-03da69801356',
  'ip'                => '127.0.0.1',
  'user_agent'        => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6)',
];

$httpClient = new HttpClient(null, null, false);
$httpClient->setSessionData($sessionData);

// Later fetching session data from client object
$sessionData = $httpClient->getSessionData();
```
