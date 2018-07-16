<?php

namespace Firesphere\HaveIBeenPwnd\Extensions;

use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
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

    protected $service;

    /**
     * @param string $pwd
     * @param Member $member
     * @param ValidationResult $valid
     * @return void
     */
    public function updateValidatePassword($pwd, $member, $valid)
    {
        $this->service = Injector::inst()->get(HaveIBeenPwndService::class);
        $allowBreach = static::config()->get('allow_pwnd');
        $breachCount = static::config()->get('pwn_treshold');
        $storeBreach = static::config()->get('save_pwnd');
        $isPwndCount = 0;
        $breached = '';

        // There's no need to check if breaches are allowed, it's a pointless excercise
        if (!$allowBreach || $breachCount !== 0) {
            $isPwndCount = $this->service->checkPwndPassword($pwd, $member);
        }

        if ($isPwndCount > 0 || $breachCount < $isPwndCount) {
            usleep(1500); // We need to conform to the FUP, max 1 request per 1500ms
            // Always mark as Pwnd if it's true
            $member->PasswordIsPwnd = $isPwndCount;
        }

        // If storing the breached sites, check the email as well
        if ($storeBreach) {
            $breached = $this->service->checkPwndEmail($member);
            $member->BreachedSites = $breached;
        }

        // Although it would be stupid, the pwnd treshold can be disabled
        // Or even allow for breached passwords. Not exactly ideal either
        if ($isPwndCount > 0 || $breached !== '') {
            $valid->addFieldError('Password',
                _t(static::class . 'KNOWN', 'Your password appears in the Have I Been Pwnd database'));
            $valid->addError($breached);
        }
    }
}
