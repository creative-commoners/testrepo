<?php

namespace CWP\Search\Tests;

use SilverStripe\Dev\FunctionalTest;

class CwpSearchPageControllerTest extends FunctionalTest
{
    protected $usesDatabase = true;

    public function testIndex()
    {
        $this->autoFollowRedirection = false;
        $result = $this->get('/search');

        $this->assertSame(302, $result->getStatusCode());
        $this->assertContains('SearchForm', $result->getHeader('Location'));
    }
}
