<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
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
        /** @var HaveIBeenPwndPage $page */
        $page = Injector::inst()->get(HaveIBeenPwndPage::class);

        /** @var HaveIBeenPwndPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, false, [$page]);

        $this->assertInstanceOf(HaveIBeenPwndPageController::class, $controller);
    }

    public function testCheckEmail()
    {
        /** @var HaveIBeenPwndPage $page */
        $page = Injector::inst()->get(HaveIBeenPwndPage::class);

        /** @var HaveIBeenPwndPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, false, [$page]);

        // Log out. Solidly I hope
        Security::setCurrentUser(null);
        Injector::inst()->get(IdentityStore::class)->logOut();

        $response = $controller->checkEmail(null);

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

        /** @var HaveIBeenPwndPageController $response */
        $response = $controller->checkEmail(null, ['handler' => $mock]);

        $this->assertContains('17Media', $response->dataRecord->Content);

        // We don't have a full set, so Yahoo shouldn't show
        $this->assertNotContains('Yahoo', $response->dataRecord->Content);
    }
}
