<?php

namespace TractorCow\ClassProxy\Generators;

use Prophecy\Doubler\NameGenerator;
use ReflectionClass;

/**
 * Uses a pre-defined name for the proxied class
 */
class ProxyNameGenerator extends NameGenerator
{
    /**
     * @var string The name to use
     */
    protected $name;

    /**
     * ProxyNameGenerator constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name(ReflectionClass $class = null, array $interfaces)
    {
        return $this->name;
    }
}
