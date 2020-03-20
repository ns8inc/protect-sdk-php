# Queue Documentation

## Purpose of the Queue Client

The purpose of the Queue Client is to allow easy access to
update messages from the NS8 Protect queue to update order
information. The NS8 Protect service sends order update
data to a First In, First Out (FIFO) queue specifically
associated with your account.

## Example of a Queue Client Implementation

The following serve as examples of implementation of the
Action Client to demonstrate intended uses:

```php
<?php
declare(strict_types=1);

use NS8\ProtectSDK\Queue\Client as QueueClient;
use MyPlatform\OrderService\Client as OrderClient;

QueueClient::initialize();
$messageArray = QueueClient::getMessages();
while ($messageArray) {
  processOrderUpdates($messageArray);
}

function processOrderUpdates(array $messageArray) {
  foreach ($messageArray as $message) {
    $order = OrderClient::getOrder(
      $message['attributes']['order_id']
    );
    $order->setStatus(
      $message['attributes']['status']
    )->save();
  }
}
```
