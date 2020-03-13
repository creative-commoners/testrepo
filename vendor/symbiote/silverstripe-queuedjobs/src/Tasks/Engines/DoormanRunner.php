<?php

namespace Symbiote\QueuedJobs\Tasks\Engines;

use AsyncPHP\Doorman\Manager\ProcessManager;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
use Symbiote\QueuedJobs\Jobs\DoormanQueuedJobTask;
use Symbiote\QueuedJobs\Services\QueuedJob;
use SilverStripe\Core\Injector\Injector;

/**
 * Runs all jobs through the doorman engine
 */
class DoormanRunner extends BaseRunner implements TaskRunnerEngine
{
    /**
     * @var string[]
     */
    protected $defaultRules = [];

    /**
     * Assign default rules for this task
     *
     * @param array $rules
     * @return $this
     */
    public function setDefaultRules($rules)
    {
        $this->defaultRules = $rules;
        return $this;
    }

    /**
     * @return array List of rules
     */
    public function getDefaultRules()
    {
        return $this->defaultRules;
    }

    /**
     * Run tasks on the given queue
     *
     * @param string $queue
     */
    public function runQueue($queue)
    {
        // split jobs out into multiple tasks...

        /** @var ProcessManager $manager */
        $manager = Injector::inst()->create(ProcessManager::class);
        $manager->setWorker(
            BASE_PATH . "/vendor/silverstripe/framework/cli-script.php dev/tasks/ProcessJobQueueChildTask"
        );
        $logPath = Environment::getEnv('SS_DOORMAN_LOGPATH');
        if ($logPath) {
            $manager->setLogPath($logPath);
        }

        // Assign default rules
        $defaultRules = $this->getDefaultRules();
        if ($defaultRules) {
            foreach ($defaultRules as $rule) {
                $manager->addRule($rule);
            }
        }

        $descriptor = $this->getNextJobDescriptorWithoutMutex($queue);

        while ($manager->tick() || $descriptor) {
            $this->logDescriptorStatus($descriptor, $queue);

            if ($descriptor instanceof QueuedJobDescriptor) {
                $descriptor->JobStatus = QueuedJob::STATUS_INIT;
                $descriptor->write();

                $manager->addTask(new DoormanQueuedJobTask($descriptor));
            }

            sleep(1);

            $descriptor = $this->getNextJobDescriptorWithoutMutex($queue);
        }
    }

    /**
     * @param string $queue
     * @return null|QueuedJobDescriptor
     */
    protected function getNextJobDescriptorWithoutMutex($queue)
    {
        $list = QueuedJobDescriptor::get()
            ->filter('JobType', $queue)
            ->sort('ID', 'ASC');

        $descriptor = $list
            ->filter('JobStatus', QueuedJob::STATUS_WAIT)
            ->first();

        if ($descriptor) {
            return $descriptor;
        }

        return $list
            ->filter('JobStatus', QueuedJob::STATUS_NEW)
            ->where(sprintf(
                '"StartAfter" < \'%s\' OR "StartAfter" IS NULL',
                DBDatetime::now()->getValue()
            ))
            ->first();
    }
}
