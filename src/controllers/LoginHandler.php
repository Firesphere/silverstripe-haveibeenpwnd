<?php

namespace Firesphere\HaveIBeenPwned\Controllers;

use Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage;
use Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService;
use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\DefaultAdminService;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\LostPasswordForm;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;

/**
 * Class LoginHandler
 * @package Firesphere\HaveIBeenPwned\Controllers
 */
class LoginHandler extends BaseLoginHandler
{

    /**
     * @var HaveIBeenPwnedService
     */
    protected $service;


    /**
     * LoginHandler constructor.
     *
     * @param string $link
     * @param MemberAuthenticator $authenticator
     */
    public function __construct($link, MemberAuthenticator $authenticator)
    {
        /** @var HaveIBeenPwnedService service */
        $this->service = Injector::inst()->get(HaveIBeenPwnedService::class);

        parent::__construct($link, $authenticator);
    }

    /**
     * @param $data
     * @param MemberLoginForm $form
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        $isDefaultAdmin = DefaultAdminService::isDefaultAdminCredentials($data['Email'], $data['Password']);
        // This is a copy-paste, because we only want to step in if the login itself is successful
        // We do not want to lock the member out immediately if the password is incorrect anyway
        // Due to a lack of a `return` option in the current extension, we need to have this copy-paste
        // before handing over to the parent
        // Also, exclude default admin from forcing a reset
        if (!$isDefaultAdmin && !HaveIBeenPwnedService::config()->get('allow_pwnd')) {
            $password = $data['Password'];
            $member = null;
            $identifierField = Member::config()->get('unique_identifier_field');
            $memberCount = Member::get()->filter([$identifierField => $data['Email']])->count();
            // There's no need to check for the member if it doesn't exist
            if ($memberCount !== 0) {
                $member = $this->checkLogin($data, $request, $result);
            }

            // How often can we find this password?
            $breachCount = $this->service->checkPwnedPassword($password);

            // Lockout the member if the breachcount is greater than 0
            if ($member && $breachCount) {
                $this->lockoutMember($member, $breachCount);
            }

            // A breached member or a non-existing member get the reset form
            if (($breachCount && $member) || !$memberCount) {
                return $this->redirectToResetPassword();
            }
        }

        // The used password to log in with is okay, and the member exists. Continue on our way
        // This does, by the way, _not_ guarantee the password is correct!
        return parent::doLogin($data, $form, $request);
    }

    /**
     * @param Member $member
     * @param Int $breachCount
     * @throws ValidationException
     */
    protected function lockoutMember($member, $breachCount)
    {
        $member->PasswordIsPwnd = $breachCount;
        $member->AutoLoginHash = null;
        $member->PasswordExpiry = '1970-01-01 00:00:00'; // To the beginning of Unixtime it is
        $member->Password = null;
        $member->write();
    }

    /**
     * Invoked if password is expired and must be changed
     *
     * @skipUpgrade
     * @return HTTPResponse
     */
    protected function redirectToResetPassword()
    {
        $lostPasswordForm = LostPasswordForm::create($this, Authenticator::class, 'lostPasswordForm');

        /** @var HaveIBeenPwnedPage|null $pwndPage */
        $pwndPage = HaveIBeenPwnedPage::get()->first();
        $lostPasswordForm->sessionMessage(
            _t(
                self::class . '.PASSWORDEXPIREDORBREACHED',
                'Because of security concerns with the password you entered, you need to reset your password. 
                Do not worry, your account has not been compromised, this is just a precaution'
            ),
            'warning'
        );

        if ($pwndPage !== null) {
            $lostPasswordForm->sessionMessage(
                _t(
                    self::class . '.PASSWORDEXPIRYREASON',
                    '<a href="{link}">You can read more here</a>',
                    ['link' => $pwndPage->Link()]
                ),
                'good'
            );
        }
        $resetPasswordLink = Security::singleton()->Link('lostpassword');

        return $this->redirect($resetPasswordLink);
    }

    /**
     * @return HaveIBeenPwnedService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param HaveIBeenPwnedService $service
     * @return LoginHandler
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }
}
