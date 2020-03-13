# SilverStripe Queued Jobs Module

[![Build Status](https://travis-ci.org/symbiote/silverstripe-queuedjobs.svg?branch=master)](https://travis-ci.org/symbiote/silverstripe-queuedjobs)
[![Scrutinizer](https://scrutinizer-ci.com/g/symbiote/silverstripe-queuedjobs/badges/quality-score.png)](https://scrutinizer-ci.com/g/symbiote/silverstripe-queuedjobs/)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)


## Maintainer Contact

Marcus Nyeholt

<marcus (at) symbiote (dot) com (dot) au>

## Requirements

* SilverStripe 4.x
* Access to create cron jobs

## Version info

The master branch of this module is currently aiming for SilverStripe 4.x compatibility

* [SilverStripe 3.1+ compatible version](https://github.com/symbiote/silverstripe-queuedjobs/tree/2.9)
* [SilverStripe 3.0 compatible version](https://github.com/symbiote/silverstripe-queuedjobs/tree/1.0)
* [SilverStripe 2.4 compatible version](https://github.com/symbiote/silverstripe-queuedjobs/tree/ss24)

## Documentation

See http://github.com/symbiote/silverstripe-queuedjobs/wiki/ for more complete
documentation

The Queued Jobs module provides a framework for SilverStripe developers to
define long running processes that should be run as background tasks.
This asynchronous processing allows users to continue using the system
while long running tasks proceed when time permits. It also lets
developers set these processes to be executed in the future.

The module comes with

* A section in the CMS for viewing a list of currently running jobs or scheduled jobs.
* An abstract skeleton class for defining your own jobs.
* A task that is executed as a cronjob for collecting and executing jobs.
* A pre-configured job to cleanup the QueuedJobDescriptor database table.

## Quick Usage Overview

* Install the cronjob needed to manage all the jobs within the system. It is best to have this execute as the
same user as your webserver - this prevents any problems with file permissions.

```
*/1 * * * * php /path/to/silverstripe/vendor/bin/sake dev/tasks/ProcessJobQueueTask
```

* If your code is to make use of the 'long' jobs, ie that could take days to process, also install another task
that processes this queue. Its time of execution can be left a little longer.

```
*/15 * * * * php /path/to/silverstripe/vendor/bin/sake dev/tasks/ProcessJobQueueTask queue=large
```

* From your code, add a new job for execution.

```php
use Symbiote\QueuedJobs\Services\QueuedJobService;

$publish = new PublishItemsJob(21);
QueuedJobService::singleton()->queueJob($publish);
```

* To schedule a job to be executed at some point in the future, pass a date through with the call to queueJob
The following will run the publish job in 1 day's time from now.

```php
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\Services\QueuedJobService;

$publish = new PublishItemsJob(21);
QueuedJobService::singleton()
    ->queueJob($publish, DBDatetime::create()->setValue(DBDatetime::now()->getTimestamp() + 86400)->Rfc2822());
```

## Using Doorman for running jobs

Doorman is included by default, and allows for asynchronous task processing.

This requires that you are running an a unix based system, or within some kind of environment
emulator such as cygwin.

In order to enable this, configure the ProcessJobQueueTask to use this backend.

In your YML set the below:


```yaml
---
Name: localproject
After: '#queuedjobsettings'
---
SilverStripe\Core\Injector\Injector:
  Symbiote\QueuedJobs\Services\QueuedJobService:
    properties:
      queueRunner: %$DoormanRunner
```

## Using Gearman for running jobs

* Make sure gearmand is installed
* Get the gearman module from https://github.com/nyeholt/silverstripe-gearman
* Create a `_config/queuedjobs.yml` file in your project with the following declaration

```yaml
---
Name: localproject
After: '#queuedjobsettings'
---
SilverStripe\Core\Injector\Injector:
  QueueHandler:
    class: Symbiote\QueuedJobs\Services\GearmanQueueHandler
```

* Run the gearman worker using `php gearman/gearman_runner.php` in your SS root dir

This will cause all queuedjobs to trigger immediate via a gearman worker (`src/workers/JobWorker.php`)
EXCEPT those with a StartAfter date set, for which you will STILL need the cron settings from above

## Using `QueuedJob::IMMEDIATE` jobs

Queued jobs can be executed immediately (instead of being limited by cron's 1 minute interval) by using
a file based notification system. This relies on something like inotifywait to monitor a folder (by
default this is SILVERSTRIPE_CACHE_DIR/queuedjobs) and triggering the ProcessJobQueueTask as above
but passing job=$filename as the argument. An example script is in queuedjobs/scripts that will run
inotifywait and then call the ProcessJobQueueTask when a new job is ready to run.

Note - if you do NOT have this running, make sure to set `QueuedJobService::$use_shutdown_function = true;`
so that immediate mode jobs don't stall. By setting this to true, immediate jobs will be executed after
the request finishes as the php script ends.

# Default Jobs

Some jobs should always be either running or queued to run, things like data refreshes or periodic clean up jobs, we call these Default Jobs.
Default jobs are checked for at the end of each job queue process, using the job type and any fields in the filter to create an SQL query e.g.

```yml
ArbitraryName:
  type: 'ScheduledExternalImportJob'
  filter:
    JobTitle: 'Scheduled import from Services'
```

Will become:

```php
QueuedJobDescriptor::get()->filter([
  'type' => 'ScheduledExternalImportJob',
  'JobTitle' => 'Scheduled import from Services'
]);
```

This query is checked to see if there's at least 1 healthly (new, run, wait or paused) job matching the filter. If there's not and recreate is true in the yml config we use the construct array as params to pass to a new job object e.g:

```yml
ArbitraryName:
  type: 'ScheduledExternalImportJob'
  filter:
    JobTitle: 'Scheduled import from Services'
  recreate: 1
  construct:
    repeat: 300
    contentItem: 100
      target: 157
```
If the above job is missing it will be recreated as:
```php
Injector::inst()->createWithArgs(ScheduledExternalImportJob::class, $construct[])
```

### Pausing Default Jobs

If you need to stop a default job from raising alerts and being recreated, set an existing copy of the job to Paused in the CMS.

### YML config

Default jobs are defined in yml config the sample below covers the options and expected values

```yaml
SilverStripe\Core\Injector\Injector:
  Symbiote\QueuedJobs\Services\QueuedJobService:
    properties:
      defaultJobs:
        # This key is used as the title for error logs and alert emails
        ArbitraryName:
          # The job type should be the class name of a job REQUIRED
          type: 'ScheduledExternalImportJob'
          # This plus the job type is used to create the SQL query REQUIRED
          filter:
            # 1 or more Fieldname: 'value' sets that will be queried on REQUIRED
            #  These can be valid ORM filter
            JobTitle: 'Scheduled import from Services'
          # Sets whether the job will be recreated or not OPTIONAL
          recreate: 1
          # Set the email address to send the alert to if not set site admin email is used OPTIONAL
          email: 'admin@email.com'
          # Parameters set on the recreated object OPTIONAL
          construct:
            # 1 or more Fieldname: 'value' sets be passed to the constructor OPTIONAL
            repeat: 300
            title: 'Scheduled import from Services'
        # Minimal implementation will send alerts but not recreate
        AnotherTitle:
          type: 'AJob'
          filter:
            JobTitle: 'A job'
```

## Configuring the CleanupJob

By default the CleanupJob is disabled. To enable it, set the following in your YML:

```yaml
Symbiote\QueuedJobs\Jobs\CleanupJob:
  is_enabled: true
```

You will need to trigger the first run manually in the UI. After that the CleanupJob is run once a day.

You can configure this job to clean up based on the number of jobs, or the age of the jobs. This is
configured with the `cleanup_method` setting - current valid values are "age" (default)  and "number".
Each of these methods will have a value associated with it - this is an integer, set with `cleanup_value`.
For "age", this will be converted into days; for "number", it is the minimum number of records to keep, sorted by LastEdited.
The default value is 30, as we are expecting days.

You can determine which JobStatuses are allowed to be cleaned up. The default setting is to clean up "Broken" and "Complete" jobs. All other statuses can be configured with `cleanup_statuses`. You can also define `query_limit` to limit the number of rows queried/deleted by the cleanup job (defaults to 100k).

The default configuration looks like this:

```yaml
Symbiote\QueuedJobs\Jobs\CleanupJob:
  is_enabled: false
  query_limit: 100000
  cleanup_method: "age"
  cleanup_value: 30
  cleanup_statuses:
    - Broken
    - Complete
```

## Health Checking

Jobs track their execution in steps - as the job runs it increments the "steps" that have been run. Periodically jobs
are checked to ensure they are healthy. This asserts the count of steps on a job is always increasing between health
checks. By default health checks are performed when a worker picks starts running a queue.

In a multi-worker environment this can cause issues when health checks are performed too frequently. You can disable the
automatic health check with the following configuration:

```yaml
Symbiote\QueuedJobs\Services\QueuedJobService:
  disable_health_check: true
```

In addition to the config setting there is a task that can be used with a cron to ensure that unhealthy jobs are
detected:

```
*/5 * * * * php /path/to/silverstripe/vendor/bin/sake dev/tasks/CheckJobHealthTask
```

## Troubleshooting

To make sure your job works, you can first try to execute the job directly outside the framework of the
queues - this can be done by manually calling the *setup()* and *process()* methods. If it works fine
under these circumstances, try having *getJobType()* return *QueuedJob::IMMEDIATE* to have execution
work immediately, without being persisted or executed via cron. If this works, next make sure your
cronjob is configured and executing correctly.

If defining your own job classes, be aware that when the job is started on the queue, the job class
is constructed _without_ parameters being passed; this means if you accept constructor args, you
_must_ detect whether they're present or not before using them. See [this issue](https://github.com/symbiote/silverstripe-queuedjobs/issues/35)
and [this wiki page](https://github.com/symbiote/silverstripe-queuedjobs/wiki/Defining-queued-jobs) for
more information.

If defining your own jobs, please ensure you follow PSR conventions, i.e. use "YourVendor" rather than "SilverStripe".

Ensure that notifications are configured so that you can get updates or stalled or broken jobs. You can
set the notification email address in your config as below:


```yaml
SilverStripe\Control\Email\Email:
  queued_job_admin_email: support@mycompany.com
```

**Long running jobs are running multiple times!**

A long running job _may_ fool the system into thinking it has gone away (ie the job health check fails because
`currentStep` hasn't been incremented). To avoid this scenario, you can set `$this->currentStep = -1` in your job's
constructor, to prevent any health checks detecting the job.

## Performance configuration

By default this task will run until either 256mb or the limit specified by php\_ini('memory\_limit') is reached.

>NOTE: This was increased to 256MB in 4.x to handle the increase in memory usage by framework.

You can adjust this with the below config change


```yaml
# Force memory limit to 256 megabytes
Symbiote\QueuedJobs\Services\QueuedJobService\QueuedJobsService:
  # Accepts b, k, m, or b suffixes
  memory_limit: 256m
```


You can also enforce a time limit for each queue, after which the task will attempt a restart to release all
resources. By default this is disabled, so you must specify this in your project as below:


```yml
# Force limit to 10 minutes
Symbiote\QueuedJobs\Services\QueuedJobService\QueuedJobsService:
  time_limit: 600
```


## Indexes

```sql
ALTER TABLE `QueuedJobDescriptor` ADD INDEX ( `JobStatus` , `JobType` )
```

## Contributing

### Translations

Translations of the natural language strings are managed through a third party translation interface, transifex.com. Newly added strings will be periodically uploaded there for translation, and any new translations will be merged back to the project source code.

Please use [https://www.transifex.com/projects/p/silverstripe-queuedjobs](https://www.transifex.com/projects/p/silverstripe-queuedjobs) to contribute translations, rather than sending pull requests with YAML files.
