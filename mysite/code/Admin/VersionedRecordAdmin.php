<?php

namespace Mysite\Project\Admin;

use Mysite\Project\Records\VersionedRecord;
use SilverStripe\Admin\ModelAdmin;

class VersionedRecordAdmin extends ModelAdmin
{
    private static $url_segment = 'versionedrecords';

    private static $managed_models = [
        VersionedRecord::class,
    ];

    private static $menu_title = 'Versioned Records';
}
