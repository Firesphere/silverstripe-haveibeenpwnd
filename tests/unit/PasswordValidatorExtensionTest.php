<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class PasswordValidatorExtensionTest extends SapphireTest
{
    public function testUpdateValidatePasswordAllowAll()
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

    public function testUpdateValidatePasswordDeny()
    {
        Config::modify()->set(PasswordValidatorExtension::class, 'allow_pwnd', false);
        Config::modify()->set(PasswordValidatorExtension::class, 'pwn_treshold', 1);
        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);

        /** @var Member $member */
        $member = Member::create();
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();

        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd123.txt');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(123, [], $body),
        ]);

        $extension->updateValidatePassword('123', $member, $valid, ['handler' => $mock]);

        $this->assertFalse($valid->isValid());
    }

    public function testUpdateValidatePasswordDenyAtTen()
    {
        Config::modify()->set(PasswordValidatorExtension::class, 'pwn_treshold', 10);

        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);

        /** @var Member $member */
        $member = Member::create();
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();

        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd1234.txt');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(123, [], $body),
            new Response(123, [], $body),
        ]);

        $extension->updateValidatePassword('1234', $member, $valid, ['handler' => $mock]);

        $this->assertTrue($valid->isValid());
        $this->assertEquals(3, $member->PasswordIsPwnd);
        $extension->updateValidatePassword('12345', $member, $valid, ['handler' => $mock]);

        $this->assertFalse($valid->isValid());
        $this->assertEquals(11, $member->PasswordIsPwnd);
    }

    protected function setUp()
    {
        Config::modify()->set(PasswordValidatorExtension::class, 'allow_pwnd', true);
        Config::modify()->set(PasswordValidatorExtension::class, 'pwn_treshold', 0);
        Config::modify()->set(PasswordValidatorExtension::class, 'save_pwnd', false);

        return parent::setUp();
    }
}
