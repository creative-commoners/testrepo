<?php

use BringYourOwnIdeas\Maintenance\Model\Package;
use BringYourOwnIdeas\SecurityChecker\Models\SecurityAlert;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;

class PopulateDummySecurityNoticeTask extends BuildTask
{
    private static $segment = 'PopulateDummySecurityNoticeTask';

    protected $title = 'Creates some dummy security notices against installed modules in the site summary report';

    public function run($request)
    {
        $createFor = ['silverstripe/cms', 'silverstripe/blog'];

        // Remove all existing alerts
        SecurityAlert::get()->removeAll();

        foreach ($createFor as $moduleName) {
            /** @var Package $module */
            $module = Package::get()->filter(['Name' => $moduleName])->first();
            if (!$module) {
                $this->output($moduleName . ' not found, skipping');
                continue;
            }

            /** @var SecurityAlert $alert */
            $alert = SecurityAlert::create();
            $alert->PackageName = $moduleName;
            $alert->Version = $module->Version;
            $alert->Title = 'Possible SQL injection attack';
            $alert->ExternalLink = 'https://www.youtube.com/watch?v=oHg5SJYRHA0';
            $alert->Identifier = 'SS-2018-123';
            $alert->PackageRecordID = $module->ID;
            $alert->write();

            $this->output('Created dummy alert for ' . $moduleName);
        }
    }

    protected function output($message)
    {
        if (Director::is_cli()) {
            echo $message, PHP_EOL;
        } else {
            echo '<p>' . $message . '</p>';
        }
    }
}
