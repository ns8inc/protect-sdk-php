## Templates Documentation

### Purpose of the Templates Client
The purpose of the Templates class is to manage communication with the NS8 Template Service. At this time this service is only used for customer order verification.

### Example Templates Client Usage

Fetching the access token for requests within the HTTP Client. By default, the HTTP Client will automatically pull the access token and does not need to be set individually for requests unless specifically told to do so.

```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Templates\Client as TemplatesClient;

// Some sample order data.
$view = 'orders-validiate';
$orderId = '0000123';
$token = 'abc';
$verificationId = '123';
$postData = null; // Change this to an array to make a POST request instead of GET.

// The variables beginning with a colon will get interpolated by the Template Service upon redirect.
$returnUri = 'http://example.org/:orderId/:token/:verificationId/:view';

$templatesClient = new TemplatesClient();
$template = $templatesClient->get($view, $orderId, $token, $verificationId, $returnUri, $postData);
```

For GET requests, the template will be available in `$template->html`.
For POST requets, the user will be redirected to the URI defined in `$template->location`.