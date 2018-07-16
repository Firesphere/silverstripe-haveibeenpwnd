<?php

namespace Firesphere\HaveIBeenPwnd\Controllers;

use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use PageController;
use SilverStripe\Core\Injector\Injector;
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
    ];

    private static $url_handlers = [
        'check-email'    => 'checkEmail',
    ];


    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkEmail()
    {
        /** @var Member|null $user */
        $user = Security::getCurrentUser();

        if ($user) {
            /** @var HaveIBeenPwndService $service */
            $service = Injector::inst()->get(HaveIBeenPwndService::class);
            $breachedEmails = $service->checkPwndEmail($user);

            $contentText = str_replace("\r\n", '<br />', $breachedEmails);

            $this->dataRecord->Content .= '<p>' . $contentText . '</p>';
        }

        return $this;
    }
}
