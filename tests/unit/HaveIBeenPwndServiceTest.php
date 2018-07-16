<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class HaveIBeenPwndServiceTest extends SapphireTest
{
    protected $member;

    public function testGetSetArgs()
    {
        $args = ['test' => 'testing'];
        $service = Injector::inst()->create(HaveIBeenPwndService::class);

        $service->setArgs($args);

        $this->assertEquals($args, $service->getArgs());
    }
    
    public function testCheckPwndPassword()
    {
        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd123.txt');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(200, [], $body),
            new Response(200, [], $body),
        ]);

        /** @var HaveIBeenPwndService $service */
        $service = Injector::inst()->createWithArgs(HaveIBeenPwndService::class, [['handler' => $mock]]);

        $response = $service->checkPwndPassword('123');

        $this->assertEquals(1014565, $response);

        $response = $service->checkPwndPassword('abc');

        $this->assertEquals(0, $response);
    }

    public function testCheckPwndEmail()
    {
        $body = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(200, [], $body),
            new Response(200, [], '[]'),
        ]);

        /** @var HaveIBeenPwndService $service */
        $service = Injector::inst()->createWithArgs(HaveIBeenPwndService::class, [['handler' => $mock]]);

        $response = $service->checkPwndEmail($this->member);

        $this->assertContains(
            'To help you identify where you have been breached, your username or email address appears in the following breaches:',
            $response
        );
        $this->member->Email = 'nonexisting@realy-i-do-not-exist.random';

        $response = $service->checkPwndEmail($this->member);

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
