<?php

namespace TractorCow\ClassProxy\Tests;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use TractorCow\ClassProxy\Proxied\Proxied;
use TractorCow\ClassProxy\ProxyFactory;

class ProxyFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $proxy = ProxyFactory::create(ProxyFactoryTest\TestClassA::class);

        /** @var ProxyFactoryTest\TestClassA $instance */
        $instance = $proxy->instance();
        $this->assertInstanceOf(ProxyFactoryTest\TestClassA::class, $instance);
        $this->assertEquals('I am a TestClassA_fa1d985', $instance->describe());
    }

    /**
     * Test class methods
     */
    public function testAbstractInstance()
    {
        $proxy = ProxyFactory::create(ProxyFactoryTest\TestClassB::class)
            ->addMethod('abstractmethod', 'return "This is an abstract method";');

        /** @var ProxyFactoryTest\TestClassB $instance */
        $instance = $proxy->instance();
        $this->assertInstanceOf(ProxyFactoryTest\TestClassB::class, $instance);
        $this->assertEquals('I am a TestClassB_4881cfb', $instance->describe());
        $this->assertEquals('This is an abstract method', $instance->abstractmethod());
    }

    /**
     * Test chained methods
     */
    public function testChainedMethods()
    {
        $proxy = ProxyFactory::create(ProxyFactoryTest\TestClassC::class)
            ->addMethod('greet', function ($args, $next) {
                return 'Firstly, ' . $next(...$args);
            })
            ->addMethod('greet', function ($args, $next) {
                return "I want to talk to you {$args[0]}, " . $next("Mr. {$args[0]}");
            });

        /** @var ProxyFactoryTest\TestClassC $instance */
        $instance = $proxy->instance();
        $this->assertInstanceOf(ProxyFactoryTest\TestClassC::class, $instance);
        $this->assertEquals('Firstly, I want to talk to you Robert, Hello Mr. Robert', $instance->greet("Robert"));
    }

    /**
     * Test methods can be proxified post-construct
     */
    public function testPostConstructProxy()
    {
        // Adding a method with no body should whitelist it for later
        $proxy = ProxyFactory::create(ProxyFactoryTest\TestClassC::class)
            ->addMethod('greet');

        /** @var ProxyFactoryTest\TestClassC|Proxied $instance */
        $instance = $proxy->instance();

        // Add methods to instance directly
        $instance->proxy()
            ->addMethod('greet', function ($args, $next) {
                return 'Firstly, ' . $next(...$args);
            })
            ->addMethod('greet', function ($args, $next) {
                return "I want to talk to you {$args[0]}, " . $next("Mr. {$args[0]}");
            });

        // Test
        $this->assertInstanceOf(ProxyFactoryTest\TestClassC::class, $instance);
        $this->assertEquals('Firstly, I want to talk to you Robert, Hello Mr. Robert', $instance->greet("Robert"));
    }

    public function testPostConstructUnexpectedError()
    {
        $proxy = ProxyFactory::create(ProxyFactoryTest\TestClassC::class);

        /** @var ProxyFactoryTest\TestClassC|Proxied $instance */
        $instance = $proxy->instance();

        // It's too late to add it by this point in time
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Method greet cannot be mocked on an instance");
        $instance
            ->proxy()
            ->addMethod('greet', function ($args, $next) {
                return 'Firstly, ' . $next(...$args);
            });
    }
}
