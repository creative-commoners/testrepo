<?php

namespace SilverStripe\Comments\Admin\CommentsGridFieldBulkAction;

use Colymba\BulkManager\BulkAction\Handler as GridFieldBulkActionHandler;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * A {@link GridFieldBulkActionHandler} for bulk marking comments as spam
 *
 * @deprecated 3.1..4.0 Abstract handlers are removed, please use concrete Spam or Approve handlers
 */
class Handler extends GridFieldBulkActionHandler
{
    private static $allowed_actions = array(
        'spam',
        'approve',
    );

    private static $url_handlers = array(
        'spam' => 'spam',
        'approve' => 'approve',
    );

    /**
     * @param  HTTPRequest $request
     * @return HTTPResponse
     */
    public function spam(HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->markSpam();
        }

        $response = new HTTPResponse(json_encode(array(
            'done' => true,
            'records' => $ids
        )));

        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }

    /**
     * @param  HTTPRequest $request
     * @return HTTPResponse
     */
    public function approve(HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->markApproved();
        }

        $response = new HTTPResponse(json_encode(array(
            'done' => true,
            'records' => $ids
        )));

        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
