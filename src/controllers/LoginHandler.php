<?php

namespace Firesphere\HaveIBeenPwnd\Controllers;


use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\LostPasswordForm;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;

class LoginHandler extends BaseLoginHandler
{

    /**
     * @var HaveIBeenPwndService
     */
    protected $service;


    public function __construct($link, MemberAuthenticator $authenticator)
    {
        /** @var HaveIBeenPwndService service */
        $this->service = Injector::inst()->get(HaveIBeenPwndService::class);

        parent::__construct($link, $authenticator);
    }

    /**
     * @param $data
     * @param MemberLoginForm $form
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        if (Director::isLive()) {
            /** @var HTTPRequest $request */

            $password = $data['Password'];

            $breachCount = $this->service->checkPwndPassword($password);

            if ($breachCount) {
                $this->lockoutMember($data, $breachCount);

                return $this->redirectToResetPassword();
            }
        }

        return parent::doLogin($data, $form, $request);
    }

    /**
     * @param $data
     * @param $breachCount
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function lockoutMember($data, $breachCount)
    {
        $loginField = Member::config()->get('unique_identifier_field');
        /** @var Member $member */
        $member = Member::get()->filter([$loginField => $data['Email']])->first();
        $member->PasswordIsPwnd = $breachCount;
        $member->AutoLoginHash = null;
        $member->PasswordExpiry = '1970-01-01 00:00:00'; // To the beginning of Unixtime it is
        $member->Password = null;
        $member->write();

        // Log the member out as well
        Injector::inst()->get(IdentityStore::class)->logOut();
    }

    /**
     * Invoked if password is expired and must be changed
     *
     * @skipUpgrade
     * @return HTTPResponse
     */
    protected function redirectToResetPassword()
    {
        $this->setupResetMessages();

        $resetPasswordLink = Security::singleton()->Link('lostpassword');

        return $this->redirect($resetPasswordLink);
    }

    /**
     * Set up messages on the Lost Password Form to inform the user of what's going on
     */
    protected function setupResetMessages()
    {
        $cp = LostPasswordForm::create($this, Authenticator::class, 'lostPasswordForm');

        $pwndPage = HaveIBeenPwndPage::get()->first();
        $cp->sessionMessage(
            _t(static::class . '.PASSWORDEXPIREDORBREACHED',
                'Because of security concerns with the password you entered, you need to reset your password.'),
            'warning'
        );

        if ($pwndPage) {
            $cp->sessionMessage(
                _t(static::class . '.PASSWORDEXPIRYREASON',
                    '<a href="{link}">You can read more here</a>',
                    ['link' => $pwndPage->Link()]),
                'good'
            );
        }
    }

    /**
     * @return HaveIBeenPwndService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param HaveIBeenPwndService $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }
}