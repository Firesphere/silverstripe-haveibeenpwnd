<?php

namespace Firesphere\HaveIBeenPwned\Controllers;

use Firesphere\HaveIBeenPwned\Extensions\MemberExtension;
use Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage;
use Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService;
use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
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
        /**
         * @var Member|MemberExtension $member
         * @var ValidationResult $result
         */
        list($member, $pwnedPasswordCount) = $this->validateMember($data, $request, $result);

        // Also, exclude default admin from forcing a reset
        if (!$isDefaultAdmin &&
            $pwnedPasswordCount &&
            !HaveIBeenPwnedService::config()->get('allow_pwnd')
        ) {
            if ($member !== null) {
                $this->lockoutMember($member, $pwnedPasswordCount);
            }
            // A breached member or unknown member get the reset form
            // It's doing both, because otherwise we'd leak data about members being registered
            return $this->redirectToResetPassword();
        }

        // The result is invalid or valid, we don't care, go to the parent
        return parent::doLogin($data, $form, $request);
    }

    /**
     * @param Member|MemberExtension $member
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

    /**
     * @param array $data
     * @param HTTPRequest $request
     * @param ValidationResult|null $result
     * @return array
     * @throws GuzzleException
     */
    protected function validateMember($data, HTTPRequest $request, &$result)
    {
        $member = $this->checkLogin($data, $request, $result);
        $password = $data['Password'];
        // How often can we find this password?
        $pwnedPasswordCount = $this->service->checkPwnedPassword($password);

        if ($member && $result->isValid()) {
            $member->PasswordIsPwnd = $pwnedPasswordCount;
        }

        return [$member, $pwnedPasswordCount];
    }
}
