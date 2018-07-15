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

        $extension->updateValidatePassword('donotcare', $member = Member::create(), $valid = ValidationResult::create());

        $this->assertTrue($valid->isValid());
        $this->assertEquals(0, $member->PasswordIsPwnd);
        $this->assertEquals('', $member->BreachedSites);
    }

    protected function setUp()
    {
        Config::modify(PasswordValidatorExtension::class, 'allow_pwnd', true);
        Config::modify(PasswordValidatorExtension::class, 'pwn_treshold', 0);
        Config::modify(PasswordValidatorExtension::class, 'save_pwnd', false);
        return parent::setUp();
    }
}
