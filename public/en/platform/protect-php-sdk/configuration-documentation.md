## Configuration Manager

### Purpose of The Configuration Manager
The Config Manager class is intended to be a flexible, functional class that can be used as a static class to manage configurable values during runtime or as a class instance to be used repeatedly throughout a method. Given this, the developmental goals of implementing the class were:

  * Avoid excessive initialization of static configurable values (e.g. avoid loading config files or fetching config data multiple times)

  * Provide a simple service that allows developers to set, check, and retrieve configurable values such as version info, environment relevant URIs, & base values required for authentication.

  * Provide a simple structure for fetching configuration data and allow configuration data to be structured in a way that is intuitive to add, read, & edit.


### Example Configuration Manager Usages
The Config Manager is explicitly designed for abstract usage. The following implementations are example usages of the Config Manager:

JSON Sample For Examples
```
{
  "version": 2,
  "platform": "Magento",
  "foo": "bar",
  "log_http_calls": true,
  "production": {
    "api_url": "https://ns8.com",
    "logging_path": "logs/production_log.log"
  },
  "testing": {
    "api_url": "https://testing.ns8.com",
    "logging_path": "logs/testing_log.log"
  },
  "development": {
    "api_url": "https://development.ns8.com",
    "logging_path": "logs/development_log.log"
  }
}
```

Initalizing the Configuration Manager and fetching if we should log HTTP calls and the API URL for the enviornment
```
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Config\Manager as ConfigManager;

// Initialize configuration manager to set environment and JSON files
$configManager = new ConfigManager('testing', null, 'base_config.json');

// Fetch a boolean value for if we should log HTTP calls
$shouldLogHttpCalls = (boolean) $configManager::getValue('log_http_calls');

// Fetch the API url for the given environment
$apiUrl = $configManager::getEnvValue('api_url');
```

Initalizing the Configuration Manager and setting then retrieving a sample value
```
<?php

declare(strict_types=1);

use NS8\ProtectSDK\Config\Manager as ConfigManager;

// Initialize configuration manager to set environment and JSON files
$configManager = new ConfigManager('testing', null, 'base_config.json');

// Set the currency value
$configManager::setValue('store.currency', 'USD');

// Later on return the curreny value
$currencyFormat = $configManager::getValue('store.currency');
```
