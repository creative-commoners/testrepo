## SilverStripe authoring tools recipe

[![Build Status](https://travis-ci.org/silverstripe/recipe-authoring-tools.svg?branch=master)](https://travis-ci.org/silverstripe/recipe-authoring-tools)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

This includes the following core SilverStripe modules:

 * [silverstripe/recipe-cms](https://github.com/silverstripe/recipe-cms): Recipe containing CMS, versioned, asset-admin, etc
 * [silverstripe/documentconverter](https://github.com/silverstripe/silverstripe-documentconverter): Imports
   OpenOffice-compatible files (doc, docx, etc) into SilverStripe pages and content
 * [silverstripe/iframe](https://github.com/silverstripe/silverstripe-iframe): Provides an IFrame page type that allows
   you to embed an IFrame into a page without resorting to custom code
 * [silverstripe/spellcheck](https://github.com/silverstripe/silverstripe-spellcheck): Improves spellcheck support for
   SilverStripe CMS, including an implementation for HunSpell
 * [silverstripe/tagfield](https://github.com/silverstripe/silverstripe-tagfield): SilverStripe module for editing tags
   (both in the CMS and other forms)
 * [silverstripe/taxonomy](https://github.com/silverstripe/silverstripe-taxonomy): Provides the capability to add and
   edit simple taxonomies within SilverStripe

This can be either added to an existing project or used as a project base for creating a basic CWP install.

## Get started

You can create a project using Composer:

```
composer create-project silverstripe/recipe-authoring-tools ./myproject ^1
```

## More information

See the [recipe plugin](https://github.com/silverstripe/recipe-plugin) page for instructions on how
SilverStripe recipes work.
