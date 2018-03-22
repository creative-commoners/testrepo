<?php

namespace Mysite\Project\Policy;

use SilverStripe\ControllerPolicy\Policies\CustomHeaderPolicy;

/**
 * This custom controller policy implementation adds a sort of hello world to home page requests
 *
 * This is configured in mysite/_config/controllerpolicy.yml
 */
class MyHomePagePolicy extends CustomHeaderPolicy
{
    public function __construct()
    {
        $this->addHeader('X-Hello-World', 'Custom controller policy: Hello world!');
    }
}
