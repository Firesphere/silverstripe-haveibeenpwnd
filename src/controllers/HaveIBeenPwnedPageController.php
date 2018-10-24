<?php

namespace Firesphere\HaveIBeenPwned\Controllers;

use Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage;
use Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

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
     * @param HTTPRequest|null $request
     * @param array $params
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkEmail($request = null, $params = [])
    {
        /** @var Member|null $user */
        $user = Security::getCurrentUser();

        if ($user) {
            /** @var HaveIBeenPwnedService $service */
            $service = Injector::inst()->createWithArgs(HaveIBeenPwnedService::class, [$params]);

            $breachedEmails = $service->checkPwndEmail($user);

            $contentText = str_replace("\r\n", '<br />', $breachedEmails);

            $this->dataRecord->Content .= '<p>' . $contentText . '</p>';
        }

        return $this;
    }
}