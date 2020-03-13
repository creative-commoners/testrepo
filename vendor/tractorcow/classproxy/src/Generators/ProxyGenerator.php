<?php

namespace TractorCow\ClassProxy\Generators;

use Closure;
use InvalidArgumentException;
use Prophecy\Doubler\Doubler;
use Prophecy\Doubler\Generator\ClassCreator;
use ReflectionClass;
use ReflectionProperty;
use TractorCow\ClassProxy\Proxied\Proxied;

/**
 * Uses prophecy to generate classes
 */
class ProxyGenerator
{
    /**
     * @var ReflectionClass
     */
    protected $parent;

    /**
     * List of method implementations.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * List of interfaces to apply to this class
     *
     * @var ReflectionClass[]
     */
    protected $interfaces = [];

    /**
     * List of custom properties. Keys are property names, values are flags
     * of ReflectionProperty.
     *
     * @var string[]
     */
    protected $properties = [];

    /**
     * Class name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Generator for code that extends a parent class
     *
     * @param ReflectionClass $parent
     */
    public function __construct(ReflectionClass $parent)
    {
        $this->parent = $parent;
        $this->regenerateName();
    }

    /**
     * Adds method to an immutable clone of this generator
     *
     * @param string $name Method name
     * @param callable|string|null $callback Callback that has args ($args, $next),
     * or string for literal code. Methods are called first-in-first-out
     * and can call `$next(...$args)` to delegate. The closure will be bound to the
     * current object, so you can use `$this` inside it.
     * If set to null, simply whitelist this method for later proxification.
     * @return static Class generator with this method
     */
    public function addMethod($name, $callback = null)
    {
        $clone = clone $this;
        if (is_string($callback)) {
            $clone->methods[$name] = $callback;
            return $clone;
        }

        // Whitelist this method for proxification
        if (!isset($clone->methods[$name])) {
            $clone->methods[$name] = [];
        }

        // Immediately register callback if provided
        if ($callback instanceof Closure) {
            $clone->methods[$name][] = $callback;
        } elseif (!is_null($callback)) {
            throw new InvalidArgumentException("Invalid callback type");
        }
        $clone->regenerateName();
        return $clone;
    }

    /**
     * Adds interface to an immutable clone of this generator
     *
     * @param ReflectionClass|string $interface
     * @return $this
     */
    public function addInterface($interface)
    {
        if (is_string($interface)) {
            $interface = new ReflectionClass($interface);
        }
        $clone = clone $this;
        $clone->interfaces[$interface->getName()] = $interface;
        $clone->regenerateName();
        return $clone;
    }

    /**
     * Add property to an immutable clone of this generator
     *
     * @param string $name
     * @param int $flags ReflectionProperty flags
     * @return ProxyGenerator
     */
    public function addProperty($name, $flags = ReflectionProperty::IS_PROTECTED)
    {
        $clone = clone $this;
        $clone->properties[$name] = $flags;
        $clone->regenerateName();
        return $clone;
    }

    /**
     * Build an instance for this class
     *
     * @param array $args
     * @return Proxied The proxied object
     */
    public function instance($args = [])
    {
        // Setup class proxy
        $proxyPatch = new ProxyClassPatch($this->interfaces, $this->properties, $this->methods);
        $doubler = new ProxyDoubler($this->name);
        $doubler->registerClassPatch($proxyPatch);

        /** @var Proxied $instance */
        $instance = $doubler->double($this->parent, $this->interfaces, $args);
        $proxy = $instance->proxy();

        // Register callbacks on instance
        foreach ($this->methods as $name => $details) {
            // Skip code blocks
            if (is_string($details)) {
                continue;
            }
            $proxy->setMethodChain($name, $details);
        }
        return $instance;
    }

    /**
     * Generate deterministic name for proxied class
     *
     * @return string The new name
     */
    protected function regenerateName()
    {
        // Seed by list of proxied properties
        $seed = json_encode([
            $this->parent->getName(),
            array_keys($this->methods),
            array_keys($this->interfaces),
            array_keys($this->properties),
        ]);

        // Build new name based on seed and base of parent class
        $suffix = substr(sha1($seed), 0, 7);
        return $this->name = $this->parent->getShortName() . '_' . $suffix;
    }
}
