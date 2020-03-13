<?php

namespace SilverStripe\SiteWideContentReport\Form;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;

/**
 * Class GridFieldBasicContentReport
 * @package SilverStripe\SiteWideContentReport\Form
 */
class GridFieldBasicContentReport extends GridField
{
    /**
     * @param int        $total
     * @param int        $index
     * @param DataObject $record
     *
     * @return array
     */
    protected function getRowAttributes($total, $index, $record)
    {
        $attributes = parent::getRowAttributes($total, $index, $record);
        $this->extend('updateRowAttributes', $total, $index, $record, $attributes);

        return $attributes;
    }
}
