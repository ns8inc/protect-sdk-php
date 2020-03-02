## Order Documentation

### Purpose of the Order Client
The purpose of the Order Client is to make it easy to retrieve order data from the NS8 Protect API.

Examples of what that can be fetched:
  * Order data (including the EQ8 Score)
  * Current merchant info

### Example Order Client Implementations
The following serve as examples of implementation of the Order Client to demonstrate intended uses:

```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Order\Client as OrderClient;

$currentMerchantInfo = OrderClient::getCurrentMerchant();
$order = OrderClient::getOrderByName('00000001');
```
