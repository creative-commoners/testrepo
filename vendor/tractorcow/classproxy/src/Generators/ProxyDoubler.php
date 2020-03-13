<?php

namespace TractorCow\ClassProxy\Generators;

use Prophecy\Doubler\Doubler;
use Prophecy\Doubler\Generator\ClassCreator;
use ReflectionClass;

class ProxyDoubler extends Doubler
{
    /**
     * Class name
     *
     * @var string
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
        parent::__construct(new ProxyMirror, new ClassCreator(), new ProxyNameGenerator($name));
    }

    protected function createDoubleClass(ReflectionClass $class = null, array $interfaces)
    {
        // Skip if class already exists
        if (class_exists($this->name, false)) {
            return $this->name;
        }
        return parent::createDoubleClass($class, $interfaces);
    }
}
