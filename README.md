# Creative Commoners test repository

## Introduciton

`master` is an empty base SilverStripe Platform project, intended to be branched
and have it's `composer.json` edited to include desired module development
branches in order for testing and demonstration to the product owner.

This project base is built from `silverstripe/installer`, but with the added
required modules and files for SilverStripe Platform support:

 - [.platform.yml](http://docs.platform.silverstripe.com/development/platform-yml-file/)
 - [`silverstripe/dynamodb`](https://github.com/silverstripe/silverstripe-dynamodb)
 - [`silverstripe/crontask`](https://github.com/silverstripe/silverstripe-crontask)

### CWP Recipe

The `cwp-installer-upgrade-testing` branch is intended for a similar usage as above,
only it contains a direct copy of the `cwp/cwp-installer` as well as the above. The
recipes aren't directly required as this would conflict in version constraints and 
fail to install anything. This way we can test individual elements of the recipe easily.

## Usage

Before using you'll need to have the base cloned somewhere (skip if already cloned):
`git clone https://github.com/creative-commoners/testrepo && cd testrepo`.
You can then proceed to build up your development/test environment on top of the initial state.

```sh
git checkout -b my/new/branch $INSTALLER_BASE
composer update
```

Where `$INSTALLER_BASE` is either `master` or `cwp-installer-upgrade-testing`.

Of course you should also `composer require` any extra modules you're working on, so you can be
installed by the platform in order to demo them to the Product Owner after deployment. This may
involve manually adding a repository to the `repositories` section of `composer.json`; see the
[composer documentation](http://vvv.tobiassjosten.net/php/have-composer-use-development-branches/) 
for guidance on this.
