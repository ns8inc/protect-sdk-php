# Action Documentation

## Table of Contents

- [Purpose of the Action Client](#purpose-of-the-action-client)
- [Actions VS Events](#actions-vs-events)
- [Example Action Client Implementations](#example-action-client-implementations)

## Purpose of the Action Client

The purpose of the Action Client is to enable calls to NS8 vi the API for
managing Event data such as fetching an Order Score and for sending Action data
such as an Order Creation. The design of the Action Client is intended to allow:

- Validation of names for known events
- The ability to pass new, unknown (to the SDK) events to the API for processing
- Interaction with the NS8 API without specifically utilizing HTTP components or
  needing to customize request logic

## Actions VS Events

As a general rule, actions occur on the platform side while events occur occur
on the NS8 Protect side. There are 3 events that are an exception to this as
well as a related action:

- ON_DISABLE_EXTENSION_EVENT: This event should be triggered when the module is
  disabled on the platform side.
- ON_ENABLE_EXTENSION_EVENT: This event should be triggered when the module is
  _re-enabled_ on the platform side. This is not needed when it is initially
  enabled.
- ON_INSTALL_PLATFORM_EVENT: This event should be triggered when the module is
  first installed.
- UNINSTALL_ACTION: This action should be set when the module is removed from
  the platform.
  Further information pre-defined actions and events is available in the
  [Switchboards Foundational Switches documentation](https://github.com/ns8inc/engineering/blob/master/integrations/switchboards_foundational_switches.md)
  and additional information regarding the differences between actions and events
  can be found in the [Switchboards Actions vs Events](https://github.com/ns8inc/engineering/blob/master/integrations/switchboards_actions_v_events.md).

## Example Action Client Implementations

The following serve as examples of implementation of the Action Client to
demonstrate intended uses:

```php
<?php
declare(strict_types=1);
use NS8\ProtectSDK\Actions\Client as ActionsClient;
$actionsClient = new ActionsClient();
// Posts a new order to NS8's API
$orderData = ['id' => 123];
$actionsClient->setAction(ActionClient::CREATE_ORDER_ACTION, $orderData);
// Posts updated merchant data to NS8's API
$merchantData = ['store_name' => 'Test Store'];
$actionsClient->setAction(ActionClient::UPDATE_MERCHANT_ACTION, $merchantData);
// Posts a triggered event from the platform
$actionsClient->triggerEvent(ActionClient::ON_DISABLE_EXTENSION_EVENT, $merchantData);
// Retrieve an NS8 order details and parse out score
$orderIncrementId = 123;
$ns8Order = $actionsClient->getEntity(sprintf('/orders/order-name/%s', base64_encode($orderIncrementId));
$fraudAssesments  = $ns8Order->fraudAssessments;
$eq8Score = array_reduce(
  $fraudAssesments,
  function (?int $foundScore, \stdClass $fraudAssessment): ?int {
    if (!empty($foundScore)) {
      return $foundScore;
    }
    return $fraudAssessment->providerType === 'EQ8' ? $fraudAssessment->score : null;
  }
);
```
