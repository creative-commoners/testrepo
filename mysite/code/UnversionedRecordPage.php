<?php

namespace Mysite\Project;

use Mysite\Project\Records\UnversionedRecord;
use Page;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

/**
 * A page that has many unversioned records, and owns them
 */
class UnversionedRecordPage extends Page
{
    private static $has_many = [
        'UnversionedRecords' => UnversionedRecord::class,
    ];

    private static $owns = ['UnversionedRecords'];

    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->addFieldToTab(
                'Root.Records',
                GridField::create(
                    'UnversionedRecords',
                    null,
                    $this->UnversionedRecords(),
                    GridFieldConfig_RecordEditor::create()
                )
            );
        });
        return parent::getCMSFields();
    }
}
