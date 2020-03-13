<?php

namespace CWP\Search\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;

/**
 * Adds field boosting capabilities to fulltext search for pages
 */
class CwpSearchBoostExtension extends DataExtension
{
    /**
     * Quality to boost the 'SearchBoost' field by.
     * Default boost is 2x
     *
     * @config
     * @var string
     */
    private static $search_boost = '2';

    private static $db = [
        'SearchBoost' => 'Text',
    ];

    /**
     * Adds boost fields to this page
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $pageInfoTitle = _t(__CLASS__ . '.PAGEINFO', 'Page info and SEO');
        $boostTitle = _t(__CLASS__ . '.SearchBoost', 'Boost Keywords');
        $boostNote = _t(
            __CLASS__ . '.SearchBoostNote',
            '(Only applies to the search results on this site e.g. not on Google search)'
        );
        $boostDescription = _t(
            __CLASS__ . '.SearchBoostDescription',
            'Enter keywords separated by comma ( , ) for which to boost the ranking of this page '
            . 'within the search results on this site.'
        );
        $boostField = TextareaField::create('SearchBoost', $boostTitle)
            ->setRightTitle($boostNote)
            ->setDescription($boostDescription);

        if ($meta = $fields->fieldByName('Root.Main.Metadata')) {
            // Rename metafield if it exists
            $meta->setTitle($pageInfoTitle);
            $fields->insertBefore('MetaDescription', $boostField);
        } else {
            // Else create new field to store SEO
            $fields->addFieldToTab(
                'Root.Main',
                ToggleCompositeField::create(
                    'Metadata',
                    $pageInfoTitle,
                    [
                        $boostField,
                    ]
                )
            );
        }
    }
}
