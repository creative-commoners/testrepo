<?php

use CWP\CWP\PageTypes\BasePage;

class Page extends BasePage
{
    private static $db = [
        'Foo' => 'Varchar', // Required for example only to ensure there's a DB table for Page
    ];
}
