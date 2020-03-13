<?php

namespace TractorCow\ClassProxy;

use ReflectionClass;
use TractorCow\ClassProxy\Generators\ProxyGenerator;

class ProxyFactory
{
    /**
     * Create new proxy builder for the given class
     *
     * @param string $class
     * @return ProxyGenerator
     */
    public static function create($class)
    {
        return new ProxyGenerator(new ReflectionClass($class));
    }
}
