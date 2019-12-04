## Description

This is the PHP SDK for NS8 Protect.

## Prerequisites

* [PHP](https://www.php.net/) 7.2+ with [Xdebug](https://xdebug.org/)
* [Composer](https://getcomposer.org/)

## Development

At the moment there isn't any real code in here, but there's a sample Demo class (and corresponding unit tests) to serve as an example.

## Installation

```bash
$ composer install
```

## Linting

NS8 uses a PHP coding standard primarily based on [Doctrine](https://github.com/doctrine/coding-standard).

To check your code for style errors:
```bash
$ composer lint
```

To automatically fix errors, when possible:
```bash
$ composer lint-fix
```

## Testing

To run the unit tests:
```bash
$ composer test
```

Code coverage must be 100%. (We'll see how long this lasts.) To verify the coverage:
```bash
$ composer test-coverage
```

## Git hooks

* The linter will check for errors before every `git commit`.
* The unit tests will run before every `git push`.

You can bypass either of these with `--no-verify` if necessary, but the CI will still run them on every merge to the `master` branch.
