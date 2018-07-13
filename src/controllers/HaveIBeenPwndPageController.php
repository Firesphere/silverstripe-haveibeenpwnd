<?php

namespace Firesphere\HaveIBeenPwnd\Controllers;

use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class \Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController
 *
 */
class HaveIBeenPwndPageController extends PageController
{
    private static $allowed_actions = [
        'checkEmail',
        'checkPassword'
    ];

    private static $url_handlers = [
        'check-email' => 'checkEmail',
        'check-password' => 'checkPassword'
    ];


    public function checkEmail(HTTPRequest $request)
    {
        /** @var Member|null $user */
        $user = Security::getCurrentUser();

        if ($user) {
            $breachedEmails = $user->checkEmail();

            $contentText = str_replace("\r\n", '<br />', $breachedEmails);

            $this->dataRecord->Content .= '<p>' . $contentText . '</p>';
        }

        return $this;
    }

    public function checkPassword(HTTPRequest $request)
    {
        // @todo
    }
}
