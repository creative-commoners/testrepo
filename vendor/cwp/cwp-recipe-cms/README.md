## CWP CMS Recipe

[![Build Status](https://travis-ci.org/silverstripe/cwp-recipe-cms.svg?branch=master)](https://travis-ci.org/silverstripe/cwp-recipe-cms)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

Extra CMS functionality recipe for a [CWP 2](https://www.cwp.govt.nz) installation. This includes the following core
SilverStripe and CWP modules:

 * [silverstripe/recipe-cms](https://github.com/silverstripe/recipe-cms): Recipe containing CMS, versioned, asset-admin, etc
 * [cwp/cwp-recipe-core](https://github.com/silverstripe/cwp-recipe-core): CWP core functionality recipe
 * [cwp/cwp](https://github.com/silverstripe/cwp): Additional CMS functionality, page types, configuration
 * [cwp/cwp-pdfexport](https://github.com/silverstripe/cwp-pdfexport): Add PDF exporting to pages
 * [silverstripe/html5](https://github.com/silverstripe/silverstripe-html5): Add HTML5 support to the CMS
 * [symbiote/silverstripe-gridfieldextensions](https://github.com/symbiote/silverstripe-gridfieldextensions): Extra
   feature components for GridFields 

This can be either added to an existing project or used as a project base for creating a
basic CWP install.

## Get started

You can create a project using Composer:

```
composer create-project cwp/cwp-recipe-cms ./cwp2-cms ^2
```

## More information

See the [recipe plugin](https://github.com/silverstripe/recipe-plugin) page for instructions on how
SilverStripe recipes work.
