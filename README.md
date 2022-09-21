# SimpleSAMLphp smartattributes module

![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-smartattributes/workflows/CI/badge.svg?branch=master)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-smartattributes/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-smartattributes)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-smartattributes/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-smartattributes/?branch=master)
[![Type Coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-smartattributes/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-smartattributes)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-smartattributes/level.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-smartattributes)

## Install

Install with composer

```bash
vendor/bin/composer require simplesamlphp/simplesamlphp-module-smartattributes
```

## Configuration

Next thing you need to do is to enable the module:

in `config.php`, search for the `module.enable` key and set `smartattributes` to true:

```php
'module.enable' => [ 'smartattributes' => true, … ],
```
