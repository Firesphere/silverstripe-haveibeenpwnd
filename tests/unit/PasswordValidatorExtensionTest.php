<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class PasswordValidatorExtensionTest extends SapphireTest
{
    public function testUpdateValidatePassword()
    {
        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);

        /** @var Member $member */
        $member = Member::create();
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();
        $extension->updateValidatePassword('donotcare', $member, $valid);

        $this->assertTrue($valid->isValid());
        $this->assertEquals(0, $member->PasswordIsPwnd);
        $this->assertEquals('', $member->BreachedSites);
    }

    protected function setUp()
    {
        Config::modify()->set(PasswordValidatorExtension::class, 'allow_pwnd', true);
        Config::modify()->set(PasswordValidatorExtension::class, 'pwn_treshold', 0);
        Config::modify()->set(PasswordValidatorExtension::class, 'save_pwnd', false);

        return parent::setUp();
    }
}
