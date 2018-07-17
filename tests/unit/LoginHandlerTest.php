<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\LoginHandler;
use Firesphere\HaveIBeenPwnd\Services\HaveIBeenPwndService;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;

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

    public function testInstantiate()
    {
        $this->assertInstanceOf(LoginHandler::class, $this->handler);
        $this->assertNotSame(BaseLoginHandler::class, get_class($this->handler));

        $this->assertInstanceOf(HaveIBeenPwndService::class, $this->handler->getService());
    }

    public function testGetSetService()
    {
        $service = Injector::inst()->get(HaveIBeenPwndService::class);
        $this->handler->setService($service);

        $this->assertInstanceOf(HaveIBeenPwndService::class, $this->handler->getService());
    }

    protected function setUp()
    {
        $this->authenticator = Injector::inst()->get(MemberAuthenticator::class);
        /** @var LoginHandler $handler */
        $this->handler = Injector::inst()->createWithArgs(LoginHandler::class, ['', $this->authenticator]);

        return parent::setUp();
    }
}
