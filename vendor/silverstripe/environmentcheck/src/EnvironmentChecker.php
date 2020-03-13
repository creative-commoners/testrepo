<?php

namespace SilverStripe\EnvironmentCheck;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Deprecation;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

/**
 * Provides an interface for checking the given EnvironmentCheckSuite.
 *
 * @package environmentcheck
 */
class EnvironmentChecker extends RequestHandler
{
    /**
     * @var array
     */
    private static $url_handlers = [
        '' => 'index',
    ];

    /**
     * @var string
     */
    protected $checkSuiteName;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $errorCode = 500;

    /**
     * @var null|string
     */
    private static $to_email_address = null;

    /**
     * @var null|string
     */
    private static $from_email_address = null;

    /**
     * @var bool
     */
    private static $email_results = false;

    /**
     * @var bool Log results via {@link \Psr\Log\LoggerInterface}
     */
    private static $log_results_warning = false;

    /**
     * @var string Maps to {@link \Psr\Log\LogLevel} levels. Defaults to LogLevel::WARNING
     */
    private static $log_results_warning_level = LogLevel::WARNING;

    /**
     * @var bool Log results via a {@link \Psr\Log\LoggerInterface}
     */
    private static $log_results_error = false;

    /**
     * @var int Maps to {@link \Psr\Log\LogLevel} levels. Defaults to LogLevel::ALERT
     */
    private static $log_results_error_level = LogLevel::ALERT;

    /**
     * @param string $checkSuiteName
     * @param string $title
     */
    public function __construct($checkSuiteName, $title)
    {
        parent::__construct();

        $this->checkSuiteName = $checkSuiteName;
        $this->title = $title;
    }

    /**
     * @param string $permission
     *
     * @throws HTTPResponse_Exception
     */
    public function init($permission = 'ADMIN')
    {
        // if the environment supports it, provide a basic auth challenge and see if it matches configured credentials
        if (Environment::getEnv('ENVCHECK_BASICAUTH_USERNAME')
            && Environment::getEnv('ENVCHECK_BASICAUTH_PASSWORD')
        ) {
            // Check that details are both provided, and match
            if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])
                || $_SERVER['PHP_AUTH_USER'] != Environment::getEnv('ENVCHECK_BASICAUTH_USERNAME')
                || $_SERVER['PHP_AUTH_PW'] != Environment::getEnv('ENVCHECK_BASICAUTH_PASSWORD')
            ) {
                // Fail check with basic auth challenge
                $response = new HTTPResponse(null, 401);
                $response->addHeader('WWW-Authenticate', "Basic realm=\"Environment check\"");
                throw new HTTPResponse_Exception($response);
            }
        } elseif (!$this->canAccess(null, $permission)) {
            // Fail check with silverstripe login challenge
            $result = Security::permissionFailure(
                $this,
                "You must have the {$permission} permission to access this check"
            );
            throw new HTTPResponse_Exception($result);
        }
    }

    /**
     * Determine if the current member can access the environment checker
     *
     * @param null|int|Member $member
     * @param string          $permission
     * @return bool
     */
    public function canAccess($member = null, $permission = 'ADMIN')
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // We allow access to this controller regardless of live-status or ADMIN permission only
        // if on CLI.  Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
        if (Director::isDev()
            || Director::is_cli()
            || empty($permission)
            || Permission::checkMember($member, $permission)
        ) {
            return true;
        }

        // Extended access checks.
        // "Veto" style, return NULL to abstain vote.
        $canExtended = null;
        $results = $this->extend('canAccess', $member);
        if ($results && is_array($results)) {
            if (!min($results)) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @return HTTPResponse
     */
    public function index()
    {
        $response = new HTTPResponse;
        $result = EnvironmentCheckSuite::inst($this->checkSuiteName)->run();

        if (!$result->ShouldPass()) {
            $response->setStatusCode($this->errorCode);
        }

        $resultText = $result->customise([
            'URL'       => Director::absoluteBaseURL(),
            'Title'     => $this->title,
            'Name'      => $this->checkSuiteName,
            'ErrorCode' => $this->errorCode,
        ])->renderWith(__CLASS__);

        if ($this->config()->get('email_results') && !$result->ShouldPass()) {
            $email = new Email(
                $this->config()->get('from_email_address'),
                $this->config()->get('to_email_address'),
                $this->title,
                $resultText
            );
            $email->send();
        }

        // Optionally log errors and warnings individually
        foreach ($result->Details() as $detail) {
            if ($this->config()->get('log_results_warning') && $detail->StatusCode == EnvironmentCheck::WARNING) {
                $this->log(
                    sprintf('EnvironmentChecker warning at "%s" check. Message: %s', $detail->Check, $detail->Message),
                    $this->config()->get('log_results_warning_level')
                );
            } elseif ($this->config()->get('log_results_error') && $detail->StatusCode == EnvironmentCheck::ERROR) {
                $this->log(
                    sprintf('EnvironmentChecker error at "%s" check. Message: %s', $detail->Check, $detail->Message),
                    $this->config()->get('log_results_error_level')
                );
            }
        }

        // output the result as JSON if requested
        if ($this->getRequest()->getExtension() == 'json'
            || strpos($this->getRequest()->getHeader('Accept'), 'application/json') !== false
        ) {
            $response->setBody($result->toJSON());
            $response->addHeader('Content-Type', 'application/json');
            return $response;
        }

        $response->setBody($resultText);

        return $response;
    }

    /**
     * Sends a log entry to the configured PSR-3 LoggerInterface
     *
     * @param string $message
     * @param int    $level
     */
    public function log($message, $level)
    {
        Injector::inst()->get(LoggerInterface::class)->log($level, $message);
    }

    /**
     * Set the HTTP status code that should be returned when there's an error.
     *
     * @param int $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @deprecated
     * @param string $from
     */
    public static function set_from_email_address($from)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        static::config()->set('from_email_address', $from);
    }

    /**
     * @deprecated
     * @return null|string
     */
    public static function get_from_email_address()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return static::config()->get('from_email_address');
    }

    /**
     * @deprecated
     * @param string $to
     */
    public static function set_to_email_address($to)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        static::config()->set('to_email_address', $to);
    }

    /**
     * @deprecated
     * @return null|string
     */
    public static function get_to_email_address()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return static::config()->get('to_email_address');
    }

    /**
     * @deprecated
     * @param bool $results
     */
    public static function set_email_results($results)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        static::config()->set('email_results', $results);
    }

    /**
     * @deprecated
     * @return bool
     */
    public static function get_email_results()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return static::config()->get('email_results');
    }
}
