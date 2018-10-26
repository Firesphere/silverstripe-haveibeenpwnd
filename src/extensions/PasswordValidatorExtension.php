<?php

namespace Firesphere\HaveIBeenPwned\Extensions;

use Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordValidator;

/**
 * Class \Firesphere\HaveIBeenPwned\Extensions\PasswordValidatorExtension
 *
 * @property PasswordValidator|PasswordValidatorExtension $owner
 */
class PasswordValidatorExtension extends Extension
{
    use Configurable;

    /**
     * @var HaveIBeenPwnedService
     */
    protected $service;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param string $pwd
     * @param Member|MemberExtension $member
     * @param ValidationResult $valid
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateValidatePassword($pwd, $member, $valid)
    {
        $this->service = Injector::inst()->createWithArgs(HaveIBeenPwnedService::class, [$this->params]);

        if (!$member->PwndDisabled) {
            $allowPwnd = HaveIBeenPwnedService::config()->get('allow_pwnd');
            $savePwnd = HaveIBeenPwnedService::config()->get('save_pwnd');

            $isPwndCount = $this->checkPwnCount($pwd, $member);
            $breached = $this->checkPwndSites($savePwnd, $member);

            // Although it would be stupid, the pwnd check can be disabled
            // Or even allow for breached passwords. Not exactly ideal
            if ($isPwndCount && !$allowPwnd) {
                $this->addMessages($valid, $isPwndCount, $breached);
            }
        }
    }

    /**
     * @param $pwd
     * @param $member
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkPwnCount($pwd, $member)
    {
        $isPwndCount = $this->service->checkPwnedPassword($pwd);

        // Always set amount of pwd's if it's true
        $member->PasswordIsPwnd = $isPwndCount;

        return $isPwndCount;
    }

    /**
     * @param $member
     * @param $savePwnd
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function checkPwndSites($savePwnd, $member)
    {
        $breached = '';
        // If storing the breached sites, check the email as well
        if ($savePwnd) {
            $breached = $this->service->checkPwnedEmail($member);
            $member->BreachedSites = $breached;
        }

        return $breached;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param ValidationResult $valid
     * @param int $isPwndCount
     * @param string $breached
     */
    protected function addMessages($valid, $isPwndCount, $breached)
    {
        $valid->addFieldError(
            'Password',
            _t(
                self::class . '.KNOWN',
                'Your password appears {times} times in the Have I Been Pwnd database',
                ['times' => $isPwndCount]
            )
        );
        if ($breached) {
            $type = $valid->isValid() ? ValidationResult::TYPE_WARNING : ValidationResult::TYPE_INFO;
            $message = _t(
                self::class . '.KNOWNBREACHMESSAGE',
                'To help you identify where you have been breached, see the HaveIBeenPwned tab for information after a successful update of your password'
            );

            $valid->addMessage($message, $type);
        }
    }
}
