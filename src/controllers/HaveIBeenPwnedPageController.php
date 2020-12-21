<?php

namespace Firesphere\HaveIBeenPwned\Controllers;

use Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

// This controller should not be initiated if the base Page doesn't exist
if (!class_exists('\Page')) {
    return;
}

/**
 * Class \Firesphere\HaveIBeenPwned\Controllers\HaveIBeenPwnedPageController
 *
 */
class HaveIBeenPwnedPageController extends PageController
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'checkEmail',
    ];

    /**
     * @var array
     */
    private static $url_handlers = [
        'check-email' => 'checkEmail',
    ];

    /**
     * @param null|HTTPRequest $request
     * @param array $params
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkEmail($request = null, $params = [])
    {
        /** @var Member|null $user */
        $user = Security::getCurrentUser();

        if ($user !== null) {
            /** @var HaveIBeenPwnedService $service */
            $service = Injector::inst()->createWithArgs(HaveIBeenPwnedService::class, [$params]);

            $breachedEmails = $service->checkPwnedEmail($user);

            $contentText = str_replace("\r\n", '<br />', $breachedEmails);


            $this->data()->Content = trim(
                $this->data()->Content . '<p><h3>We found the following breaches for your account:</h3>' .
                $contentText .
                '</p>'
            );
        }

        return $this;
    }
}
