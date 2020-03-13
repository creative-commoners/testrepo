# Taxonomy

[![Build Status](https://img.shields.io/travis/silverstripe/silverstripe-taxonomy.svg)](http://travis-ci.org/silverstripe/silverstripe-taxonomy)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/silverstripe/silverstripe-taxonomy.svg)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-taxonomy/?branch=master)
[![Code Coverage](https://img.shields.io/codecov/c/github/silverstripe/silverstripe-taxonomy.svg)](https://codecov.io/gh/silverstripe/silverstripe-taxonomy)

## Introduction

The Taxonomy module add the capability to add and edit simple taxonomies within SilverStripe.

## Requirements

 * SilverStripe 4.0+
 * SilverStripe Admin Module 1.0.2+
 
 **Note:** this version is compatible with SilverStripe 4. For SilverStripe 3, please see [the 1.x release line](https://github.com/silverstripe/silverstripe-taxonomy/tree/1.2).

## Features

Create multiple taxonomies with any number of nested terms.

## Installation

```
$ composer require silverstripe/taxonomy
```
Afterwards run `/dev/build?flush=all` to rebuild your database.

For usage instructions see [user manual](docs/en/userguide/index.md).

## Contributing

### Translations

Translations of the natural language strings are managed through a third party translation interface, transifex.com. Newly added strings will be periodically uploaded there for translation, and any new translations will be merged back to the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-taxonomy](https://www.transifex.com/projects/p/silverstripe-taxonomy) to contribute translations, rather than sending pull requests with YAML files.

## Reporting Issues

Please [create an issue](http://github.com/silverstripe/silverstripe-taxonomy/issues) for any bugs you've found, or features you're missing.
