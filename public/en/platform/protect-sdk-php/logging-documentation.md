# Logging Client

## Purpose of the Logging Client

The logging client is intended to provide a simple class that can be
instantiated and utilized to log information to multiple sources. The logging
client relies on [Monolog](https://github.com/Seldaek/monolog) in addition to
[Monolog Cascade](https://github.com/theorchard/monolog-cascade) to provide a
configurable, flexible interface in which NS8 can supply logging components in
addition to custom logging components supplied by NS8 SDK users. The Logging
Client provides methods for:

* Errors: Logs errors including a Throwable object for context
* Debugging: Logs debugging info
* Warnings: Logs warning generated during runtime
* Info: Logs information set for debugging or performance insight

The format of the log information, the destination it is sent to, and the log
level required for events to be logged are all set via a configuration file. If
needed, this configuration can be dynamically generated and set during runtime
through a PHP array with the same keys as the configuration file.

## Logging Formatters

Logging formatters provide the structure of how logged information should be
normalized and presented in their final output. Custom formatters can be created
by implementing the `Monolog\Formatter\FormatterInterface` interface or existing
formatters can be applied.

## Logging Processors

Logging processors provide additional information for logging events in a
similar fashion to Traits in PHP. Processors can add context for branch info,
memory usage, network information, etc. and can be filtered to specific log
levels in custom processor implementations. Custom processors can be created by
implementing the `Monolog\Processor\ProcessorInterface` interface.

## Logging Handlers

Logging handlers detail the main "functionality" of an intended logging
operation and specify such attributes of the Log process as:

* The handler class being used (e.g. `Monolog\Handler\StreamHandler`)
* The minimum error level needed to be logged with the given handler
* The formatter to be used when logging information
* The processors to be included when invoking a logging event

Multiple handlers are made available by Monolog and custom handlers can be
created by implementing the `Monolog\Handler\HandlerInterface` interface or one
of the `HandlerInterface` abstract implementation classes such as
`Monolog\Handler\AbstractProcessingHandler`.  The handler's primary methods will
be `handle` which determines if the entry should be logged and what conditionals
must be met before writing and `write` which performs the actual sending of log
information to the destination.
[Monolog's Handler directory](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/)
contains multiple developed handlers that can be reviewed for a deeper
undestanding as well.

## Example Logger Implementations

The following JSON is an example Cascade configuration based off of
[Cascade's usage guidelines](https://github.com/theorchard/monolog-cascade).

```json
{
  "version": 1,
  "disable_existing_loggers": false,
  "formatters": {
    "spaced": {
      "class": "Monolog\\Formatter\\LineFormatter",
      "format": "%datetime% %channel%.%level_name%  %message%\n",
      "include_stacktraces": true
    },
    "dashed": {
      "format": "%datetime%-%channel%.%level_name% - %message%\n"
    }
  },
  "handlers": {
    "console": {
      "class": "Monolog\\Handler\\StreamHandler",
      "level": "DEBUG",
      "formatter": "spaced",
      "stream": "php://stdout"
    },
    "api_handler": {
      "class": "NS8\\Handler\\ApiHandler",
      "level": "INFO",
      "formatter": "dashed",
      "stream": "./example_info.log"
    },
  },
  "loggers": {
    "loggerA": {
      "handlers": [
        "console",
        "api_handler"
      ]
    }
  }
}

The following demonstrates usages utilizing the Logger class:
```php
<?php
declare(strict_types=1);
use NS8\ProtectSDK\Logging\Client as LoggingClient;

$logger = new LoggingClient();

// Log an exception
$exception = new \Exception();
$logger->error('Here is some error information', $exception, [
  'additional_data' => 'goes_here'
]);

// Log a debugging statement
$logger->debug('Here is some debugging information', [
  'additional_data' => 'goes_here'
]);

// Log a warning statement
$logger->warn('Here is a warning statement', [
  'additional_data' => 'goes_here'
]);

// Log an informational statement
$logger->info('Here in an information statement', [
  'additional_data' => 'goes_here'
]);
```
