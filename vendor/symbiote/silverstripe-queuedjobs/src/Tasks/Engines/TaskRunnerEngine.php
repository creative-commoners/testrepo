<?php

namespace Symbiote\QueuedJobs\Tasks\Engines;

/**
 * Runs tasks on a queue
 */
interface TaskRunnerEngine
{
    /**
     * Run tasks on the given queue
     *
     * @param string $queue
     */
    public function runQueue($queue);
}
