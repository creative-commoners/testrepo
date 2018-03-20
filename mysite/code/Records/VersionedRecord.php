<?php

namespace Mysite\Project\Records;

use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

class VersionedRecord extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $table_name = 'VersionedRecord';

    private static $has_one = [
        'Page' => Page::class,
    ];

    private static $extensions = [
        Versioned::class,
    ];

    private static $versioning = [
        Versioned::DRAFT,
        Versioned::LIVE,
    ];
}
