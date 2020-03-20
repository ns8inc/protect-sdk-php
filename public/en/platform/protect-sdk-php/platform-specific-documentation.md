# Platform Specific Documentation

## What is platform specific in the SDK & why

The SDK is intended to be as flexible and dynamic as possible to provide easy
usage within any PHP environment. However, aspects of the SDK have been added to
accomodate exceptionally popular frameworks or use-cases.

### Magento Specific Methods

* `NS8\ProtectSDK\Security\Client::getPlatformAccessTokenEndpoint`: This method
  is tailored to the Magento access token retrieval at this point in time. The
  structure of method and the ability to pass in a platform name as a parameter
  provides the opportunity to expand the use of this endpoint and make it usable
  across multiple popular platforms in the future.
