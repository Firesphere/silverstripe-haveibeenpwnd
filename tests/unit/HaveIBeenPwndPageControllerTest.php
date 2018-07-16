<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class HaveIBeenPwndPageControllerTest extends SapphireTest
{
    public function testClassExists()
    {
        $page = Injector::inst()->get(HaveIBeenPwndPage::class);

        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, $page);

        $this->assertInstanceOf(HaveIBeenPwndPageController::class, $controller);
    }

    public function testCheckEmail()
    {
        $page = Injector::inst()->get(HaveIBeenPwndPage::class);

        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, true, [$page]);

        // Log out. Solidly I hope
        Security::setCurrentUser(null);
        Injector::inst()->get(IdentityStore::class)->logOut();

        $response = $controller->checkEmail();

        // If there's no user, it should just return itself
        $this->assertInstanceOf(HaveIBeenPwndPageController::class, $response);

        $member = Member::create(['Email' => 'test@test.com']);
        Security::setCurrentUser($member);
        $body = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);

        /** @var HaveIBeenPwndPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, false, [$page]);

        $response = $controller->checkEmail(['handler' => $mock]);

        $this->assertContains('17Media', $response->Content);

        // We don't have a full set, so Yahoo shouldn't show
        $this->assertNotContains('Yahoo', $response->Content);
    }
}
