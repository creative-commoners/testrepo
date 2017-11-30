# Creative Commoners test repository

`master` is an empty base SilverStripe Platform project, intended to be branched
and have it's `composer.json` edited to include desired module development
branches in order for testing and demonstration to the product owner.

This project base is built from `silverstripe/installer`, but with the added
required modules and files for SilverStripe Platform support:

 - [.platform.yml](http://docs.platform.silverstripe.com/development/platform-yml-file/)
 - [`silverstripe/dynamodb`](https://github.com/silverstripe/silverstripe-dynamodb)
 - [`silverstripe/crontask`](https://github.com/silverstripe/silverstripe-crontask)

## Usage

Before using you'll need to have the base cloned somewhere; `git clone https://github.com/creative-commoners/testrepo && cd testrepo`. Then you can proceed to build up your development/test environment on top of the platform ready initial state.

```sh
git checkout -b my/new/branch master
composer update
```

Of course you should also `composer require` all the modules you're working on, so you can actually work on them/demo to the PO after deployment ;)
