# Database proxy

Ok, so you want to proxy the database.

Install this module, and decorate the factory with code you want to extend

```
---
Name: myproxydb
After: '#proxydb'
---
TractorCow\SilverStripeProxyDB\ProxyDBFactory:
  extensions:
    - ProxyDBExtension
```

Then in your code you can do this

```
<?php

use SilverStripe\Core\Extension;
use TractorCow\ClassProxy\Generators\ProxyGenerator;

class ProxyDBExtension extends Extension
{
    public function updateProxy(ProxyGenerator &$proxy)
    {
        $proxy = $proxy->addMethod('manipulate', function ($args, $next) {
            SearchManipulator::manipulate($args[0]);
            return $next(...$args);
        });
    }
}
```

You can chain methods; All addMethod() calls on the same method name will 
form a set of middleware. First methods registered are executed first.
