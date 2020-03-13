## CWP Core Recipe

[![Build Status](https://travis-ci.org/silverstripe/cwp-recipe-core.svg?branch=master)](https://travis-ci.org/silverstripe/cwp-recipe-core)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

Core functionality only recipe for a [CWP 2.0](https://www.cwp.govt.nz) installation. This includes the following core
SilverStripe and CWP modules:

 * [recipe-core](https://github.com/silverstripe/recipe-core): Recipe containing framework, config, assets
 * [cwp-core](https://github.com/silverstripe/cwp-core): CWP basic compatibility module
 * [auditor](https://github.com/silverstripe/silverstripe-auditor): Provides audit trail logging for various events in
   the system
 * [environmentcheck](https://github.com/silverstripe/silverstripe-environmentcheck): Adds automated checks to monitor
   an environment's health status
 * [hybridsessions](https://github.com/silverstripe/silverstripe-hybridsessions): Hybrid cookie/database session store for SilverStripe
 * [mimevalidator](https://github.com/silverstripe/silverstripe-mimevalidator): Checks uploaded file content roughly
   matches a known MIME type for the file extension

This can be either added to an existing project or used as a project base for creating a
basic CWP core-only install.

## Get started

You can create a project using Composer:

```
composer create-project cwp/cwp-recipe-core ./cwp2-core ^2
```

## More information

See the [recipe plugin](https://github.com/silverstripe/recipe-plugin) page for instructions on how
SilverStripe recipes work.
