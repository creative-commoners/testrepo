<?php

namespace SilverStripe\GraphQL\Auth;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Security;

/**
 * SilverStripe default member authenticator
 *
 * @internal Experimental API
 *
 * In most configurations, this will retrieve the current user from the session data.
 * This means that client needs to send the session cookie to the server, which means
 * that if it's a client session
 *
 * Outside of access by the CMS, this is unlikely to be the best authenticator, and
 * it's likely to be replaced in a future alpha/beta release
 */
class MemberAuthenticator implements AuthenticatorInterface
{
    public function authenticate(HTTPRequest $request)
    {
        return Security::getCurrentUser();
    }

    /**
     * Determine if this authenticator is applicable to the current request
     *
     * @param HTTPRequest $request
     * @return bool
     */
    public function isApplicable(HTTPRequest $request)
    {
        $user = Security::getCurrentUser();
        return !empty($user);
    }
}
