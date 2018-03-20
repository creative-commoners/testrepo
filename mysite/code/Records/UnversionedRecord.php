<?php

namespace Mysite\Project\Records;

use Page;
use SilverStripe\ORM\DataObject;

class UnversionedRecord extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $table_name = 'UnversionedRecord';

    private static $has_one = [
        'Page' => Page::class,
    ];
}
