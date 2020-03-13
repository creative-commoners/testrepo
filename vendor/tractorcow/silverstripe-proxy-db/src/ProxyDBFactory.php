<?php

namespace TractorCow\SilverStripeProxyDB;

use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Factory;
use TractorCow\ClassProxy\ProxyFactory;

class ProxyDBFactory implements Factory
{
    use Extensible;

    /**
     * Creates a new service instance.
     *
     * @param string $service The class name of the service.
     * @param array $params The constructor parameters.
     * @return object The created service instances.
     */
    public function create($service, array $params = array())
    {
        $proxy = ProxyFactory::create($service);
        $this->extend('updateProxy', $proxy);
        $instance = $proxy->instance($params);
        $this->extend('updateInstance', $instance);
        return $instance;
    }
}
