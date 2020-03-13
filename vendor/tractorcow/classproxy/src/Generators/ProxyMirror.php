<?php

namespace TractorCow\ClassProxy\Generators;

use InvalidArgumentException;
use Prophecy\Doubler\Generator\ClassMirror;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Exception\Doubler\ClassMirrorException;
use ReflectionClass;

/**
 * Mirror that doesn't mirror any methods by default
 */
class ProxyMirror extends ClassMirror
{
    public function reflect(ReflectionClass $class = null, array $interfaces)
    {
        $node = new ClassNode;

        if (null !== $class) {
            if ($class->isInterface()) {
                throw new InvalidArgumentException(sprintf(
                    "Could not reflect %s as a class, because it\n" .
                    "is interface - use the second argument instead.",
                    $class->getName()
                ));
            }
            if ($class->isFinal()) {
                throw new ClassMirrorException(
                    sprintf('Could not reflect class %s as it is marked final.', $class->getName()),
                    $class
                );
            }

            $node->setParentClass($class->getName());
        }

        foreach ($interfaces as $interface) {
            if (!$interface instanceof ReflectionClass) {
                throw new InvalidArgumentException(sprintf(
                    "[ReflectionClass \$interface1 [, ReflectionClass \$interface2]] array expected as\n" .
                    "a second argument to `ClassMirror::reflect(...)`, but got %s.",
                    is_object($interface) ? get_class($interface) . ' class' : gettype($interface)
                ));
            }
            if (false === $interface->isInterface()) {
                throw new InvalidArgumentException(sprintf(
                    "Could not reflect %s as an interface, because it\n" .
                    "is class - use the first argument instead.",
                    $interface->getName()
                ));
            }

            // Add interface
            $node->addInterface($interface->getName());
        }

        return $node;
    }
}
