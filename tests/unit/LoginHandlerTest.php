<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\LoginHandler;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\LostPasswordForm;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

class LoginHandlerTest extends SapphireTest
{
    /**
     * @var LoginHandler
     */
    protected $handler;

    /**
     * @var MemberAuthenticator
     */
    protected $authenticator;

    protected $memberId;

    public function testInstantiate()
    {
        $this->assertInstanceOf(LoginHandler::class, $this->handler);
        $this->assertNotSame(BaseLoginHandler::class, get_class($this->handler));

        $this->assertInstanceOf(HaveIBeenPwndService::class, $this->handler->getService());
    }

    public function testGetSetService()
    {
        $service = Injector::inst()->get(HaveIBeenPwndService::class);
        $response = $this->handler->setService($service);

        $this->assertInstanceOf(HaveIBeenPwndService::class, $this->handler->getService());
        $this->assertInstanceOf(LoginHandler::class, $response);
    }

    public function testDoLogin()
    {
        $body = file_get_contents(__DIR__ . '/../fixtures/pwnd123.txt');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.

        $mock = new MockHandler([
            new Response(123, [], $body),
        ]);

        $this->handler->getService()->setArgs(['handler' => $mock]);

        $form = MemberLoginForm::create(Controller::curr(), get_class($this->authenticator), 'LoginForm');
        /** @var HTTPRequest $request */
        $request = Injector::inst()->createWithArgs(HTTPRequest::class, ['GET', '/login']);
        $request->setSession(Injector::inst()->createWithArgs(Session::class, [['bla' => 'bla']]));
        $this->handler->setRequest($request);

        $response = $this->handler->doLogin(['Email' => 'test@test.com', 'Password' => '1234567890'], $form, $request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotContains('lostpassword', $response->getHeader('location'));

        Config::modify()->set(HaveIBeenPwndService::class, 'allow_pwnd', false);
        $response = $this->handler->doLogin(['Email' => 'test@test.com', 'Password' => '1234567890'], $form, $request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('lostpassword', $response->getHeader('location'));
        Injector::inst()->get(IdentityStore::class)->logOut();

        $response = $this->handler->doLogin(['Email' => 'test@test.com', 'Password' => '12345678'], $form, $request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotContains('lostpassword', $response->getHeader('location'));
        $passwordForm = LostPasswordForm::create($this->handler, Authenticator::class, 'lostPasswordForm');

        $this->assertContains('You can read more here', $passwordForm->getMessage());
    }

    protected function setUp()
    {
        Config::modify()->set(HaveIBeenPwndService::class, 'allow_pwnd', true);
        Config::modify()->set(HaveIBeenPwndService::class, 'save_pwnd', false);

        HaveIBeenPwndPage::create(['Title' => 'I am pwnd'])->write();

        $member = Member::create(['Email' => 'test@test.com', 'Password' => '1234567890']);
        $this->memberId = $member->write();
        $this->authenticator = Injector::inst()->get(MemberAuthenticator::class);
        /** @var LoginHandler $handler */
        $this->handler = Injector::inst()->createWithArgs(LoginHandler::class, ['', $this->authenticator]);

        return parent::setUp();
    }

    protected function tearDown()
    {
        Member::get()->byID($this->memberId)->delete();
        HaveIBeenPwndPage::get()->filter(['Title' => 'I am pwnd'])->first()->delete();
        parent::tearDown();
    }
}
