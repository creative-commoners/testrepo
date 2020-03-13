<?php

namespace TractorCow\ClassProxy\Generators;

use Closure;
use Exception;
use Prophecy\Doubler\ClassPatch\ClassPatchInterface;
use Prophecy\Doubler\Generator\ClassMirror;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use TractorCow\ClassProxy\Proxied\Proxied;

class ProxyClassPatch implements ClassPatchInterface
{
    /**
     * List of method implementations.
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
     * Patch the implementation with the given content
     *
     * ProxyClassPatch constructor.
     *
     * @param ReflectionClass[] $interfaces
     * @param array $properties
     * @param array $methods
     */
    public function __construct($interfaces, $properties, $methods)
    {
        $this->interfaces = $interfaces;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function supports(ClassNode $node)
    {
        return true;
    }

    /**
     * Applies patch to the specific class node.
     *
     * @param ClassNode $node
     * @return void
     */
    public function apply(ClassNode $node)
    {
        $this->applyProperties($node);
        $this->applyInterfaces($node);
        $this->applyMethods($node);
    }

    /**
     * Returns patch priority, which determines when patch will be applied.
     *
     * @return int Priority number (higher - earlier)
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * Copies a reflection method into a node, and returns it
     *
     * @param ReflectionMethod $method
     * @param ClassNode $node
     * @return MethodNode
     */
    public function mirrorMethod(ReflectionMethod $method, ClassNode $node)
    {
        if ($method->isStatic()) {
            throw new Exception("No static method mocking yet");
        }

        // Note: This code is bad but saves many lines of duplication
        $mirror = new ClassMirror();
        $mirrorReflection = new ReflectionClass($mirror);
        $reflectMethodToNode = $mirrorReflection->getMethod('reflectMethodToNode');
        $reflectMethodToNode->setAccessible(true);
        $reflectMethodToNode->invoke($mirror, $method, $node);
        return $node->getMethod($method->getName());
    }

    /**
     * @param ClassNode $node
     */
    protected function applyProperties(ClassNode $node)
    {
        // Add all properties
        foreach ($this->properties as $name => $flags) {
            switch (true) {
                case $flags & ReflectionProperty::IS_PROTECTED === ReflectionProperty::IS_PROTECTED:
                    $visibility = 'protected';
                    break;
                case $flags & ReflectionProperty::IS_PRIVATE === ReflectionProperty::IS_PRIVATE:
                    $visibility = 'private';
                    break;
                default:
                    $visibility = 'public';
            }
            $node->addProperty($name, $visibility);
        }
        $node->addProperty('proxy', 'protected');
    }

    /**
     * @param ClassNode $node
     */
    protected function applyInterfaces(ClassNode $node)
    {
        // Add all interfaces
        foreach ($this->interfaces as $interface) {
            $node->addInterface($interface->getName());
        }
        $node->addInterface(Proxied::class);
    }

    /**
     * @param ClassNode $node
     * @throws Exception
     */
    protected function applyMethods(ClassNode $node)
    {
        $parent = new ReflectionClass($node->getParentClass());

        // Add all methods
        foreach ($this->methods as $name => $method) {
            // Mirror method
            $methodNode = $this->mirrorMethod($parent->getMethod($name), $node);
            $code = $this->generateMethodBody($method, $methodNode->getReturnType(), $name);
            $methodNode->setCode($code);
        }

        // Add proxy method
        $proxyGetter = new MethodNode('proxy');
        $proxyGetter->setVisibility('public');
        $proxyGetter->setCode(<<<'CODE'
    if (!$this->proxy) {
        $this->proxy = new \TractorCow\ClassProxy\Proxied\ProxiedBehaviour($this);
    }
    return $this->proxy;
CODE
        );
        $node->addMethod($proxyGetter);
    }

    /**
     * Generate code for method body
     *
     * @param string|Closure $method Implementation
     * @param string $returnType Return type
     * @param string $name Method name
     * @return Closure|string
     */
    protected function generateMethodBody($method, $returnType, $name)
    {
        // Build body for this method
        if (is_string($method)) {
            return $method;
        }

        // Check if we should return or not
        $returner = $returnType === 'void' ? '' : 'return ';

        // Generate proxy method
        return <<<"CODE"
    {$returner}\$this->proxy()->invoke(
        __FUNCTION__,
        func_get_args(),
        function(...\$args) {
            {$returner}parent::{$name}(...\$args);
        }
    );
CODE;
    }
}
