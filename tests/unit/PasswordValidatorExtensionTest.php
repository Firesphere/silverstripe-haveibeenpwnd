<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension;
use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class PasswordValidatorExtensionTest extends SapphireTest
{
    public function testUpdateValidatePasswordAllowAll()
    {
        Config::modify()->set(HaveIBeenPwndService::class, 'allow_pwnd', true);
        Config::modify()->set(HaveIBeenPwndService::class, 'save_pwnd', false);
        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd1234.txt');
        $body2 = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(123, [], $body),
            new Response(123, [], $body2),
        ]);

        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);

        /** @var Member $member */
        $member = Member::create();
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();
        $extension->updateValidatePassword('123', $member, $valid, null, ['handler' => $mock]);

        $this->assertTrue($valid->isValid());
        $this->assertEquals(1014565, $member->PasswordIsPwnd);
        $this->assertEquals('', $member->BreachedSites);
    }

    public function testUpdatePasswordValidateTemporarily()
    {
        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);
        /** @var Member $member */
        $member = Member::create(['PwndDisabled' => 'true']);
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();
        $extension->updateValidatePassword('password', $member, $valid, null);

        $this->assertTrue($valid->isValid());
        $this->assertEquals(0, $member->PasswordIsPwnd);
        $this->assertEquals('', $member->BreachedSites);
    }

    public function testUpdateValidatePasswordDeny()
    {
        Config::modify()->set(HaveIBeenPwndService::class, 'allow_pwnd', false);
        Config::modify()->set(HaveIBeenPwndService::class, 'save_pwnd', false);
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

        $extension->updateValidatePassword('123', $member, $valid, null, ['handler' => $mock]);

        $this->assertFalse($valid->isValid());
    }

    public function testStoredBreached()
    {
        Config::modify()->set(HaveIBeenPwndService::class, 'allow_pwnd', false);
        Config::modify()->set(HaveIBeenPwndService::class, 'save_pwnd', false);


        /** @var PasswordValidatorExtension $extension */
        $extension = Injector::inst()->get(PasswordValidatorExtension::class);

        /** @var Member $member */
        $member = Member::create(['Email' => 'test@test.com']);
        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();

        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd1234.txt');
        $body2 = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(123, [], $body),
            new Response(123, [], $body2),
        ]);

        $extension->updateValidatePassword('1234', $member, $valid, null, ['handler' => $mock]);

        $messages = $valid->getMessages();

        $this->assertCount(1, $messages);
        $this->assertEmpty($member->BreachedSites);

        Config::modify()->set(HaveIBeenPwndService::class, 'save_pwnd', true);

        /** @var ValidationResult $valid */
        $valid = ValidationResult::create();

        $mock = new MockHandler([
            new Response(123, [], $body),
            new Response(123, [], $body2),
        ]);

        $extension->updateValidatePassword('1234', $member, $valid, null, ['handler' => $mock]);

        $messages = $valid->getMessages();

        $this->assertContains('2fast4u', $member->BreachedSites);
        $this->assertContains('2fast4u', $messages[1]['message']);
    }

    protected function setUp()
    {
        return parent::setUp(); // TODO: Change the autogenerated stub
    }

    protected function tearDown()
    {
        /** @var Member|null $member */
        $member = Member::get()->filter(['Email' => 'test@test.com'])->first();
        if ($member) {
            $member->delete();
        }
        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
