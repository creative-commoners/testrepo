<?php

namespace Mysite\Project\Records;

use Page;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
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

    public function canView($member = null)
    {
        if (Permission::check('CMS_ACCESS_Mysite\\Project\\Admin\\VersionedRecordAdmin')) {
            return true;
        }
        return parent::canView($member);
    }

    public function canEdit($member = null)
    {
        if (Permission::check('CMS_ACCESS_Mysite\\Project\\Admin\\VersionedRecordAdmin')) {
            return true;
        }
        return parent::canEdit($member);
    }

    public function canCreate($member = null, $context = [])
    {
        if (Permission::check('CMS_ACCESS_Mysite\\Project\\Admin\\VersionedRecordAdmin')) {
            return true;
        }
        return parent::canCreate($member, $context);
    }

    public function canDelete($member = null)
    {
        if (Permission::check('CMS_ACCESS_Mysite\\Project\\Admin\\VersionedRecordAdmin')) {
            return true;
        }
        return parent::canDelete($member);
    }
}
