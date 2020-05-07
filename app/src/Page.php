<?php

namespace {

    use SilverStripe\AssetAdmin\Forms\UploadField;
    use SilverStripe\CMS\Model\SiteTree;
    use SilverStripe\Assets\File;

    class Page extends SiteTree
    {
        private static $db = [];

        private static $has_one = [
            'MyUploadField' => File::class
        ];

        public function getCMSFields()
        {
            $fields = parent::getCMSFields();
            $fields->addFieldToTab('Root.Main', new UploadField('MyUploadField'));
            return $fields;
        }
    }
}
