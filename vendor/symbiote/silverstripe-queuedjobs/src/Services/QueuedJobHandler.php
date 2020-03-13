<?php

namespace Symbiote\QueuedJobs\Services;

use Monolog\Handler\AbstractProcessingHandler;
use SilverStripe\Core\Injector\Injectable;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;

/**
 * Writes log output to a job descriptor
 */
class QueuedJobHandler extends AbstractProcessingHandler
{
    use Injectable;

    /** @var QueuedJob */
    protected $job;

    /** @var QueuedJobDescriptor */
    protected $jobDescriptor;

    public function __construct(QueuedJob $job, QueuedJobDescriptor $jobDescriptor)
    {
        parent::__construct();

        $this->job = $job;
        $this->jobDescriptor = $jobDescriptor;
    }

    /**
     * @return QueuedJob
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @return QueuedJobDescriptor
     */
    public function getJobDescriptor()
    {
        return $this->jobDescriptor;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        $this->handleBatch([$record]);
    }

    public function handleBatch(array $records)
    {
        foreach ($records as $record) {
            $this->job->addMessage($record['message'], $record['level_name'], $record['datetime']);
        };
        $this->jobDescriptor->SavedJobMessages = serialize($this->job->getJobData()->messages);

        $this->jobDescriptor->write();
    }
}
