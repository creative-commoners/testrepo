<?php

namespace Symbiote\QueuedJobs\Tasks\Engines;

use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * Class BaseRunner
 *
 * @author dmooyman
 */
class BaseRunner
{
    /**
     * Returns an instance of the QueuedJobService.
     *
     * @return QueuedJobService
     */
    public function getService()
    {
        return QueuedJobService::singleton();
    }

    /**
     * Write in a format expected by the output medium (CLI/HTML).
     *
     * @param string $line Line to be written out, without the newline character.
     * @param null|string $prefix
     */
    protected function writeLogLine($line, $prefix = null)
    {
        if (!$prefix) {
            $prefix = '[' . DBDatetime::now()->Rfc2822() . '] ';
        }

        if (Director::is_cli()) {
            echo $prefix . $line . "\n";
        } else {
            echo Convert::raw2xml($prefix . $line) . "<br>";
        }
    }

    /**
     * Logs the status of the queued job descriptor.
     *
     * @param bool|null|QueuedJobDescriptor $descriptor
     * @param string $queue
     */
    protected function logDescriptorStatus($descriptor, $queue)
    {
        if (is_null($descriptor)) {
            $this->writeLogLine('No new jobs on queue ' . $queue);
        }

        if ($descriptor === false) {
            $this->writeLogLine('Job is still running on queue ' . $queue);
        }

        if ($descriptor instanceof QueuedJobDescriptor) {
            $this->writeLogLine('Running ' . $descriptor->JobTitle . ' and others from queue ' . $queue . '.');
        }
    }

    /**
     * Logs the number of current jobs per queue
     */
    public function listJobs()
    {
        $service = $this->getService();
        for ($i = 1; $i <= 3; $i++) {
            $jobs = $service->getJobList($i);
            $num = $jobs ? $jobs->Count() : 0;
            $this->writeLogLine('Found ' . $num . ' jobs for mode ' . $i . '.');
        }
    }
}
