<?php

namespace Symbiote\QueuedJobs\Services;

use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;

/**
 * Default method for handling items run via the cron
 *
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class DefaultQueueHandler
{
    public function startJobOnQueue(QueuedJobDescriptor $job)
    {
        $job->activateOnQueue();
    }

    public function scheduleJob(QueuedJobDescriptor $job, $date)
    {
        // noop
    }
}
