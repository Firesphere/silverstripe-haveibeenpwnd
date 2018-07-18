<?php

namespace Firesphere\HaveIBeenPwnd\Extensions;

use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordValidator;

/**
 * Class \Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension
 *
 * @property PasswordValidator|PasswordValidatorExtension $owner
 */
class PasswordValidatorExtension extends Extension
{
    use Configurable;

    /**
     * @var HaveIBeenPwndService
     */
    protected $service;

    /**
     * @param string $pwd
     * @param Member $member
     * @param ValidationResult $valid
     * @param array $params
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateValidatePassword($pwd, $member, $valid, $params = [])
    {
        $this->service = Injector::inst()->createWithArgs(HaveIBeenPwndService::class, [$params]);

        $allowPwnd = HaveIBeenPwndService::config()->get('allow_pwnd');
        $savePwnd = HaveIBeenPwndService::config()->get('save_pwnd');

        $isPwndCount = $this->checkPwnCount($pwd, $member, $allowPwnd);
        $breached = $this->checkPwndSites($member, $savePwnd);


        // Although it would be stupid, the pwnd check can be disabled
        // Or even allow for breached passwords. Not exactly ideal
        if ($isPwndCount && !$allowPwnd) {
            $valid->addFieldError(
                'Password',
                _t(static::class . 'KNOWN', 'Your password appears in the Have I Been Pwnd database')
            );
            if ($breached) {
                $message = _t(
                    static::class . 'KNOWN_BREACH_PLUS_BREACHES',
                    "To help you identify where you have been breached, your username or email address appears in the following breaches:\r\n"
                );

                $valid->addError($message . $breached);
            }
        }
    }

    /**
     * @param $pwd
     * @param $member
     * @param $allowPwnd
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkPwnCount($pwd, $member, $allowPwnd)
    {
        $isPwndCount = 0;
        // There's no need to check if breaches are allowed, it's a pointless excercise
        if (!$allowPwnd) {
            $isPwndCount = $this->service->checkPwndPassword($pwd);
        }

        // Always mark as Pwnd if it's true
        $member->PasswordIsPwnd = $isPwndCount;

        return $isPwndCount;
    }

    /**
     * @param $member
     * @param $savePwnd
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkPwndSites($member, $savePwnd)
    {
        $breached = '';
        // If storing the breached sites, check the email as well
        if ($savePwnd) {
            usleep(1500); // We need to conform to the FUP, max 1 request per 1500ms
            $breached = $this->service->checkPwndEmail($member);
            $member->BreachedSites = $breached;
        }

        return $breached;
    }
}
