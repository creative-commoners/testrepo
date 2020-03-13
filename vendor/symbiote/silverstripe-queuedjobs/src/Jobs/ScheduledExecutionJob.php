<?php

namespace Symbiote\QueuedJobs\Jobs;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * A job that gets executed on a particular schedule. When it runs,
 * it will call the onScheduledExecution method on the owning
 * dataobject.
 *
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class ScheduledExecutionJob extends AbstractQueuedJob
{
    /**
     * @param DataObject $dataObject
     * @param int $timesExecuted
     */
    public function __construct($dataObject = null, $timesExecuted = 0)
    {
        if ($dataObject) {
            $this->objectID = $dataObject->ID;
            $this->objectType = $dataObject->ClassName;

            // captured so we have a unique hash generated for this job
            $this->timesExecuted = $timesExecuted;
            $this->totalSteps = 1;
        }
    }

    /**
     * @return DataObject
     */
    public function getDataObject()
    {
        return DataObject::get_by_id($this->objectType, $this->objectID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return _t(
            __CLASS__ . '.Title',
            'Scheduled execution for {title}',
            array('title' => $this->getDataObject()->getTitle())
        );
    }


    public function setup()
    {
    }

    public function process()
    {
        $object = $this->getDataObject();
        if ($object) {
            $object->onScheduledExecution();

            // figure out what our rescheduled date should be
            $timeStr = $object->ExecuteFree;
            if ($object->ExecuteEvery) {
                $executeInterval = $object->ExecuteInterval;
                if (!$executeInterval || !is_numeric($executeInterval)) {
                    $executeInterval = 1;
                }
                $timeStr = '+' . $executeInterval . ' ' . $object->ExecuteEvery;
            }

            $next = strtotime($timeStr);
            if ($next > DBDatetime::now()->getTimestamp()) {
                // in the future
                $nextGen = DBDatetime::create()->setValue($next)->Rfc2822();
                $nextId = QueuedJobService::singleton()->queueJob(
                    Injector::inst()->create(ScheduledExecutionJob::class, $object, $this->timesExecuted + 1),
                    $nextGen
                );
                $object->ScheduledJobID = $nextId;
                $object->write();
            }
        }

        $this->currentStep++;
        $this->isComplete = true;
    }
}
