<?php

namespace Firesphere\HaveIBeenPwnd\Controllers;

use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class \Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController
 *
 * @property HaveIBeenPwndPage $data
 * @property HaveIBeenPwndPage $dataRecord
 */
class HaveIBeenPwndPageController extends PageController
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
            /** @var HaveIBeenPwndService $service */
            $service = Injector::inst()->createWithArgs(HaveIBeenPwndService::class, [$params]);

            $breachedEmails = $service->checkPwndEmail($user);

            $contentText = str_replace("\r\n", '<br />', $breachedEmails);

            $this->dataRecord->Content .= '<p>' . $contentText . '</p>';
        }

        return $this;
    }
}
