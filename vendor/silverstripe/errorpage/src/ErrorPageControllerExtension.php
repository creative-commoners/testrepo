<?php

namespace SilverStripe\ErrorPage;

use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;

/**
 * Enhances error handling for a controller with ErrorPage generated output
 */
class ErrorPageControllerExtension extends Extension
{

    /**
     * Used by {@see RequestHandler::httpError}
     *
     * @param int $statusCode
     * @param HTTPRequest $request
     * @throws HTTPResponse_Exception
     */
    public function onBeforeHTTPError($statusCode, $request, $errorMessage = null)
    {
        if (Director::is_ajax()) {
            return;
        }
        $response = ErrorPage::response_for($statusCode, $errorMessage);
        if ($response) {
            throw new HTTPResponse_Exception($response, $statusCode);
        }
    }
}
