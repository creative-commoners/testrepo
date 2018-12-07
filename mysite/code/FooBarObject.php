<?php

use SilverStripe\ORM\DataObject;

class FooBarObject extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
        'Description' => 'Text',
        'Category' => 'Enum("A,B,C")',
        'BirthYear' => 'Year'
    ];

    public function forTemplate()
    {
        return $this->renderWith(['FooBarObject']);
    }
}
