# Registry module

[![Build Status](http://img.shields.io/travis/silverstripe/silverstripe-registry.svg?style=flat)](https://travis-ci.org/silverstripe/silverstripe-registry)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-registry/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-registry/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-registry/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-registry)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

## Requirements

 * SilverStripe ^4.0

**Note:** For a SilverStripe 3.x compatible version, please use [the 1.x release line](https://github.com/silverstripe/silverstripe-registry/tree/1.0).

## Installation

Install with Composer:

```
composer require silverstripe/registry
```

When the module is installed, run a `dev/build` in your browser, or from the command line via `vendor/bin/sake dev/build`.

## Instructions

See [developer documentation](docs/en/index.md) for more setup details.

[User documentation](docs/en/userguide/index.md)

## Known issues

PostgreSQL databases might have problems with searches, as queries done using `LIKE` are case sensitive.
