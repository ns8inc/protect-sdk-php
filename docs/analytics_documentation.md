# Analytics Documentation

## Table of Contents

- [Purpose of the Analytics Client](#purpose-of-the-analytics-client)
- [Example Analytics Client Implementation](#example-analytics-client-implementation)

## Purpose of the Analytics Client

The purpose of the Analytics Client is to offer easy-to-use methods to render
NS8 analytics components for front-end use. Specifically, this class is designed
to return JavaScript snippets that should be rendered on customer-facing pages
to analyze the session and site usage.

## Example Analytics Client Implementation

The following serve as examples of implementation of the Action Client to
demonstrate intended uses:

```php
<?php
declare(strict_types=1);
use NS8\ProtectSDK\Analytics\Client as AnalyticsClient;
// Init SDK configuration so SDK is ready for use.
$this->config->initSdkConfiguration();
$script = AnalyticsClient::getTrueStatsScript();
echo is_string($script) ? sprintf('<script>%s</script>', $script) : '';
```
