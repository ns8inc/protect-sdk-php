## Action Documentation

### Purpose of the Action Client
The purpose of the Action Client is to enable calls to NS8 vi the API for managing Event data such as fetching an Order Score and for sending Action data such as an Order Creation. The design of the Action Client is intended to allow:
  * Validation of names for known events
  * The ability to pass new, unknown (to the SDK) events to the API for processing
  * Interaction with the NS8 API without specifically utilizing HTTP components or needing to customize request logic

### Example Action Client Implementations
The following serve as examples of implementation of the Action Client to demonstrate intended uses:
```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Action\Client as ActionClient;

$actionClient = new ActionClient();

// Posts a new order to NS8's API
$orderData = ['id' => 123];
$actionClient->set(ActionClient::CREATE_ORDER_ACTION, $orderData);

// Posts updated merchant data to NS8's API
$merchantData = ['store_name' => 'Test Store'];
$actionClient->set(ActionClient::UPDATE_MERCHANT_ACTION, $merchantData);

// Retrieve an NS8 order details and parse out score
$orderIncrementId = 123;
$ns8Order = $actionClient->get(sprintf('/orders/order-name/%s', base64_encode($orderIncrementId));
$fraudAssesments  = $ns8Order->fraudAssessments;
$eq8Score = array_reduce($fraudAssesments, function (?int $foundScore, \stdClass $fraudAssessment): ?int {
            if (!empty($foundScore)) {
                return $foundScore;
            }
            return $fraudAssessment->providerType === 'EQ8' ? $fraudAssessment->score : null;
        });
```
