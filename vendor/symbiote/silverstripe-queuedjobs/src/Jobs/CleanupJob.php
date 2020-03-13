<?php

namespace Symbiote\QueuedJobs\Jobs;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * An queued job to clean out the QueuedJobDescriptor Table
 * which often gets too full
 *
 * @author Andrew Aitken-Fincham <andrew@symbiote.com.au>
 */
class CleanupJob extends AbstractQueuedJob
{
    use Configurable;

    /**
     * How we will determine "stale"
     * Possible values: age, number
     * @config
     * @var string
     */
    private static $cleanup_method = "age";

    /**
     * Value associated with cleanupMethod
     * age => days, number => integer
     * @config
     * @var integer
     */
    private static $cleanup_value = 30;

    /**
     * Which JobStatus values are OK to be deleted
     * @config
     * @var array
     */
    private static $cleanup_statuses = array(
        "Complete",
        "Broken",
        // "Initialising",
        // "Running",
        // "New",
        // "Paused",
        // "Cancelled",
        // "Waiting",
    );

    /**
     * Database query limit
     *
     * @config
     * @var integer
     */
    private static $query_limit = 100000;

    /**
     * Check whether is enabled or not for BC
     * @config
     * @var boolean
     */
    private static $is_enabled = false;

    /**
     * Defines the title of the job
     * @return string
     */
    public function getTitle()
    {
        return _t(
            __CLASS__ . '.Title',
            "Clean up old jobs from the database"
        );
    }

    /**
     * Set immediacy of job
     * @return int
     */
    public function getJobType()
    {
        $this->totalSteps = '1';
        return QueuedJob::IMMEDIATE;
    }

    /**
     * Clear out stale jobs based on the cleanup values
     */
    public function process()
    {
        // construct limit statement if query_limit is valid int value
        $limit = '';
        $query_limit = $this->config()->get('query_limit');
        if (is_numeric($query_limit) && $query_limit >= 0) {
            $limit = ' LIMIT ' . ((int)$query_limit);
        }

        $statusList = implode('\', \'', $this->config()->get('cleanup_statuses'));
        switch ($this->config()->get('cleanup_method')) {
            // If Age, we need to get jobs that are at least n days old
            case "age":
                $cutOff = date(
                    "Y-m-d H:i:s",
                    strtotime(DBDatetime::now() .
                        " - " .
                        $this->config()->cleanup_value .
                        " days")
                );
                $stale = DB::query(
                    'SELECT "ID"
					FROM "QueuedJobDescriptor"
					WHERE "JobStatus"
					IN (\'' . $statusList . '\')
					AND "LastEdited" < \'' . $cutOff . '\'' . $limit
                );
                $staleJobs = $stale->column("ID");
                break;
            // If Number, we need to save n records, then delete from the rest
            case "number":
                $fresh = DB::query(
                    'SELECT "ID"
					FROM "QueuedJobDescriptor"
					ORDER BY "LastEdited"
					ASC LIMIT ' . $this->config()->cleanup_value
                );
                $freshJobIDs = implode('\', \'', $fresh->column("ID"));

                $stale = DB::query(
                    'SELECT "ID"
					FROM "QueuedJobDescriptor"
					WHERE "ID"
					NOT IN (\'' . $freshJobIDs . '\')
					AND "JobStatus"
					IN (\'' . $statusList . '\')' . $limit
                );
                $staleJobs = $stale->column("ID");
                break;
            default:
                $this->addMessage("Incorrect configuration values set. Cleanup ignored");
                $this->isComplete = true;
                return;
        }
        if (empty($staleJobs)) {
            $this->addMessage("No jobs to clean up.");
            $this->isComplete = true;
            $this->reenqueue();
            return;
        }
        $numJobs = count($staleJobs);
        $staleJobs = implode('\', \'', $staleJobs);
        DB::query('DELETE FROM "QueuedJobDescriptor"
			WHERE "ID"
			IN (\'' . $staleJobs . '\')');
        $this->addMessage($numJobs . " jobs cleaned up.");
        // let's make sure there is a cleanupJob in the queue
        $this->reenqueue();
        $this->isComplete = true;
    }

    private function reenqueue()
    {
        if ($this->config()->get('is_enabled')) {
            $this->addMessage("Queueing the next Cleanup Job.");
            $cleanup = Injector::inst()->create(CleanupJob::class);
            QueuedJobService::singleton()->queueJob(
                $cleanup,
                DBDatetime::create()->setValue(DBDatetime::now()->getTimestamp() + 86400)->Rfc2822()
            );
        }
    }
}
