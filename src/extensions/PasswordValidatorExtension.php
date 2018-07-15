<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 15-Jul-18
 * Time: 12:02
 */

namespace Firesphere\HaveIBeenPwnd\Extensions;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

/**
 * Class \Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension
 *
 * @property PasswordValidator|PasswordValidatorExtension $owner
 */
class PasswordValidatorExtension extends Extension
{
    use Configurable;

    /**
     * @param string $pwd
     * @param Member $member
     * @param ValidationResult $valid
     * @return void
     */
    public function updateValidatePassword($pwd, $member, $valid)
    {
        $allowBreach = static::config()->get('allow_pwnd');
        $breachCount = static::config()->get('pwn_treshold');
        $storeBreach = static::config()->get('save_pwnd');
        $isPwndCount = 0;
        $breached = '';

        // There's no need to check if breaches are allowed, it's a pointless excercise
        if (!$allowBreach || $breachCount !== 0) {
            $isPwndCount = $member->checkPwndPassword($pwd);
        }

        if ($isPwndCount > 0 || $breachCount < $isPwndCount) {
            usleep(1500); // We need to conform to the FUP, max 1 request per 1500ms
            // Always mark as Pwnd if it's true
            $member->PasswordIsPwnd = $isPwndCount;
        }

        // If storing the breached sites, check the email as well
        if ($storeBreach) {
            $breached = $member->checkPwndEmail($valid);
            $member->BreachedSites = $breached;
        }

        // Although it would be stupid, the pwnd treshold can be disabled
        // Or even allow for breached passwords. Not exactly ideal either
        if ($isPwndCount > 0 || $breached !== '') {
            $valid->addFieldError('Password', _t(static::class . 'KNOWN', 'Your password appears in the Have I Been Pwnd database'));
            $valid->addError($breached);
        }
    }
}
