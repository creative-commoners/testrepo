<?php

namespace TractorCow\ClassProxy\Proxied;

use Closure;
use InvalidArgumentException;

/**
 * Provides instance-specific proxification once a class instance has been
 * scaffolded and constructed.
 */
class ProxiedBehaviour
{
    /**
     * @var Proxied
     */
    protected $owner = null;

    /**
     * List of methods to array of chained callbacks
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Container for proxy behaviour belonging to a parent class
     *
     * @param Proxied $owner Proxied class
     */
    public function __construct(Proxied $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Invoke a method
     *
     * @param string $method
     * @param array $args
     * @param callable $last
     * @return mixed
     */
    public function invoke($method, $args, $last)
    {
        // Prevent unregistered methods being called
        if (!isset($this->methods[$method])) {
            throw new InvalidArgumentException("Method {$method} is not proxied");
        }
        $next = $last;
        foreach (array_reverse($this->methods[$method]) as $callable) {
            $callable = Closure::bind($callable, $this->owner);
            $next = function (...$args) use ($callable, $next) {
                return $callable($args, $next);
            };
        }
        return $next(...$args);
    }

    /**
     * Set a list of methods. This will whitelist a method for injection.
     *
     * @param string $method
     * @param Closure[] $chain
     */
    public function setMethodChain($method, $chain)
    {
        $this->methods[$method] = $chain;
    }

    /**
     * Add another method callback
     *
     * @param string $method
     * @param Closure $callback
     * @return ProxiedBehaviour
     */
    public function addMethod($method, $callback)
    {
        // Prevent uncallable methods being registered
        if (!isset($this->methods[$method])) {
            throw new InvalidArgumentException("Method {$method} cannot be mocked on an instance");
        }
        $this->methods[$method][] = $callback;
        return $this;
    }
}
