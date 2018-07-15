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

/**
 * Class \Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension
 *
 * @property PasswordValidator|PasswordValidatorExtension $owner
 */
class PasswordValidatorExtension extends Extension
{
    use Configurable;

    /**
     * @param $pwd
     * @param ValidationResult $valid
     * @return void
     */
    public function updateValidatePassword($pwd, $member, $valid)
    {
        $allowBreach = static::config()->get('allow_pwnd');
        $breachCount = static::config()->get('pwn_treshold');
        $storeBreach = static::config()->get('save_pwnd');
        $breached = '';
        $isPwndCount = $member->checkPwndPassword($pwd);

        if ($isPwndCount > 0) {
            usleep(1500); // We need to conform to the FUP, max 1 request per 1500ms
            // Always mark as Pwnd if it's true
            $member->PasswordIsPwnd = $isPwndCount;
            $breached = $member->checkPwndEmail($valid);
        }

        if ($storeBreach) {
            $member->BreachedSites = $breached;
        }

        // Although it would be stupid, the pwnd treshold can be disabled
        // Or even allow for breached passwords. Not exactly ideal either
        if (!$allowBreach || ($isPwndCount > $breachCount && $breachCount !== 0)) {
            $valid->addError(_t(static::class . 'KNOWN', 'Your password appears in the Have I Been Pwnd database'));
            $valid->addError($breached);
        }
    }
}
