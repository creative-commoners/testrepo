testrepo is full of completely separate VCS trees.

The standard approach is to:

```
create-project cwp/cwp-recipe-kitchen-sink
git init
git branch <my-version>
git remote add testrepo
git push
```

You may also wish to just re-use the last used branch, e.g.

xyz

```
git checkout cwp-2.5.1-rc1
git checkout -b cwp-2.5.2-rc1
rm composer.lock
rm -rf vendor
composer install
```

Just make sure that the composer.json is still correct before doing this

a
