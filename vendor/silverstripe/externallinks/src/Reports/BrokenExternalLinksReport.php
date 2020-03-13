<?php

namespace SilverStripe\ExternalLinks\Reports;

use SilverStripe\Core\Convert;
use SilverStripe\ExternalLinks\Model\BrokenExternalPageTrackStatus;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Reports\Report;
use SilverStripe\View\Requirements;

/**
 * Content side-report listing pages with external broken links
 * @package externallinks
 */

class BrokenExternalLinksReport extends Report
{

    /**
     * Returns the report title
     *
     * @return string
     */
    public function title()
    {
        return _t(__CLASS__ . '.EXTERNALBROKENLINKS', "External broken links report");
    }

    public function columns()
    {
        return array(
            "Created" => "Checked",
            'Link' => array(
                'title' => 'External Link',
                'formatting' => function ($value, $item) {
                    return sprintf(
                        '<a target="_blank" href="%s">%s</a>',
                        Convert::raw2att($item->Link),
                        Convert::raw2xml($item->Link)
                    );
                }
            ),
            'HTTPCodeDescription' => 'HTTP Error Code',
            "Title" => array(
                "title" => 'Page link is on',
                'formatting' => function ($value, $item) {
                    $page = $item->Page();
                    return sprintf(
                        '<a href="%s">%s</a>',
                        Convert::raw2att($page->CMSEditLink()),
                        Convert::raw2xml($page->Title)
                    );
                }
            )
        );
    }

    /**
     * Alias of columns(), to support the export to csv action
     * in {@link GridFieldExportButton} generateExportFileData method.
     * @return array
     */
    public function getColumns()
    {
        return $this->columns();
    }

    public function sourceRecords()
    {
        $track = BrokenExternalPageTrackStatus::get_latest();
        if ($track) {
            return $track->BrokenLinks();
        }
        return ArrayList::create();
    }

    public function getCMSFields()
    {
        Requirements::css('silverstripe/externallinks: css/BrokenExternalLinksReport.css');
        Requirements::javascript('silverstripe/externallinks: javascript/BrokenExternalLinksReport.js');

        $fields = parent::getCMSFields();

        $runReportButton = FormAction::create('createReport', _t(__CLASS__ . '.RUNREPORT', 'Create new report'))
            ->addExtraClass('btn-primary external-links-report__create-report')
            ->setUseButtonTag(true);
        $fields->push($runReportButton);

        $reportResultSpan = '<p class="external-links-report__report-progress"></p>';
        $reportResult = LiteralField::create('ResultTitle', $reportResultSpan);
        $fields->push($reportResult);

        return $fields;
    }
}
