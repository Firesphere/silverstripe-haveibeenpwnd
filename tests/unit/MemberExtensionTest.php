<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Extensions\MemberExtension;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Security\Member;

class MemberExtensionTest extends SapphireTest
{
    /**
     * @var Member
     */
    protected $member;

    public function testUpdateCMSFields()
    {
        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('PasswordIsPwnd'));

        $this->member->BreachedSites = '000error, test';

        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('BreachedSites'));
        $this->assertTrue($fields->hasTabSet('HaveIBeenPwnd'));
    }

    public function testCheckPwndPassword()
    {
        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd123.txt');
        // This sets up the mock client to respond to the first request it gets
        // with an HTTP 200 containing your mock json body.
        $mock = new MockHandler([
            new Response(200, [], $body),
            new Response(200, [], $body),
        ]);

        /** @var MemberExtension $extension */
        $extension = Injector::inst()->get(MemberExtension::class);
        $extension->setOwner($this->member);

        $response = $extension->checkPwndPassword('123', ['handler' => $mock]);

        $this->assertEquals(1014565, $response);

        $response = $extension->checkPwndPassword('abc', ['handler' => $mock]);

        $this->assertEquals(0, $response);
    }

    public function testCheckPwndEmail()
    {
        $body = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the first request it gets
        // with an HTTP 200 containing your mock json body.
        $mock = new MockHandler([
            new Response(200, [], $body),
            new Response(200, [], '[]'),
        ]);

        /** @var MemberExtension $extension */
        $extension = Injector::inst()->get(MemberExtension::class);
        $extension->setOwner($this->member);

        $response = $extension->checkPwndEmail(['handler' => $mock]);

        $this->assertContains('To help you identify where you have been breached, your username or email address appears in the following breaches:', $response);
        $this->member->Email = 'nonexisting@realy-i-do-not-exist.random';

        $response = $extension->checkPwndEmail(['handler' => $mock]);

        $this->assertEquals('', $response);
    }

    protected function setUp()
    {
        $this->member = Member::create([
            'Email'          => 'test@test.com',
            'PasswordIsPwnd' => 0,
            'Password'       => '1234567890' // I is good password
        ]);

        return parent::setUp();
    }
}
