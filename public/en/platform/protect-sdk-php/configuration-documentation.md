## Configuration Manager

### Purpose of The Configuration Manager
The Config Manager class is intended to be a flexible, functional class that can be used as a static class to manage configurable values during runtime or as a class instance to be used repeatedly throughout a method. Given this, the developmental goals of implementing the class were:

  * Avoid excessive initialization of static configurable values (e.g. avoid loading config files or fetching config data multiple times)

  * Provide a simple service that allows developers to set, check, and retrieve configurable values such as version info, environment relevant URIs, & base values required for authentication.

  * Provide a simple structure for fetching configuration data and allow configuration data to be structured in a way that is intuitive to add, read, & edit.


### Example Configuration Manager Usages
The Config Manager is explicitly designed for abstract usage. It is important to note that **values are shared across all instances of the Config Manager class statically** to permit easy access to these values throughout runtime - The first time this class is initialized or used, the `initConfiguration` method should be invoked to set-up environmental and base values. The following implementations are example usages of the Config Manager:

JSON Sample For Examples
```json
{
  "version": 2,
  "platform": "Magento",
  "foo": "bar",
  "log_http_calls": true,
  "production": {
    "urls": {
      "client_url": "https://protect-client.ns8.com/",
      "api_url": "https://protect.ns8.com/"
    }
  },
  "testing": {
    "urls": {
      "client_url": "https://test-protect-client.ns8.com/",
      "api_url": "https://test-protect.ns8.com/"
    }
  },
  "development": {
    "api_url": "https://development.ns8.com",
    "logging_path": "logs/development_log.log"
  }
}
```

Initalizing the Configuration Manager and fetching if we should log HTTP calls and the API URL for the enviornment
```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Config\Manager as ConfigManager;

// Initialize configuration manager as an object and set environment and JSON files
$configManager = new ConfigManager();
$configManager::initConfiguration('testing', null, 'base_config.json')

// Fetch a boolean value for if we should log HTTP calls
$shouldLogHttpCalls = (boolean) $configManager::getValue('log_http_calls');

// Fetch the API url for the given environment
$apiUrl = $configManager::getEnvValue('api_url');
```

Setting then retrieving a sample value
```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Config\Manager as ConfigManager;

// Set the currency value
ConfigManager::setValue('store.currency', 'USD');

// Later on return the curreny value
ConfigManager::getValue('store.currency');
```

Checking if a config value exists
```php
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Config\Manager as ConfigManager;

// Does Foo config value exist?
$doesFooExist = ConfigManager::doesValueExist('foo');
```
