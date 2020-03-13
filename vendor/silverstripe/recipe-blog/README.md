## Overview

Provides a quick baseline installation for a blog and some common supporting
features.

## Getting started

To start a new website with a blog installed;

```
composer create-project silverstripe/recipe-blog website ^1
```

Or to include a blog into your existing website:

```
composer require silverstripe/recipe-blog
```

## Included functionality

This recipe includes `silverstripe/blog` and it's dependencies as one might
expect, but also provides a set of optional functionality enabled by the
presence of:

* [`silverstripe/widgets`](https://github.com/silverstripe/silverstripe-widgets): Add widgets
* [`silverstripe/content-widget`](https://github.com/silverstripe/silverstripe-content-widget): Display HTML content in a widget
* [`silverstrpe/spamprotection`](https://github.com/silverstripe/silverstripe-spamprotection): Add spam protection to SilverStripe forms
* [`silverstripe/akismet`](https://github.com/silverstripe/silverstripe-akismet): Use Akismet to add a simple spam filter 
* [`silverstripe/comments`](https://github.com/silverstripe/silverstripe-comments): Add commenting functionality for Pages and other DataObjects
* [`silverstripe/comment-notifications`](https://github.com/silverstripe/comment-notifications): Add simple email notification functionality for when new visitor comments are posted
* [`colymba/gridfield-bulk-editing-tools`](https://github.com/colymba/GridFieldBulkEditingTools): Facilitate bulk file upload & record editing
* [`silverstripe/lumberjack`](https://github.com/silverstripe/silverstripe-lumberjack): Easily manage pages in GridFields

And includes some configuration out of the box.
