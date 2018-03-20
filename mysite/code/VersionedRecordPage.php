<?php

namespace Mysite\Project;

use Mysite\Project\Records\VersionedRecord;
use Page;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

/**
 * A page that has many versioned records, and owns them
 */
class VersionedRecordPage extends Page
{
    private static $has_many = [
        'VersionedRecords' => VersionedRecord::class,
    ];

    private static $owns = ['VersionedRecords'];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->addFieldToTab(
                'Root.Records',
                GridField::create(
                    'VersionedRecords',
                    null,
                    $this->VersionedRecords(),
                    GridFieldConfig_RecordEditor::create()
                )
            );
        });
        return parent::getCMSFields();
    }
}
