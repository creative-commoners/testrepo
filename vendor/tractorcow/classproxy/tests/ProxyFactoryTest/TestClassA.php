<?php

namespace TractorCow\ClassProxy\Tests\ProxyFactoryTest;

class TestClassA
{
    public function describe()
    {
        return 'I am a ' . get_class($this);
    }
}
