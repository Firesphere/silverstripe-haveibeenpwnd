<?php

namespace Firesphere\HaveIBeenPwned\Tests;

use Firesphere\HaveIBeenPwned\Controllers\HaveIBeenPwnedPageController;
use Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class HaveIBeenPwnedPageControllerTest extends SapphireTest
{
    public function testClassExists()
    {
        /** @var HaveIBeenPwnedPage $page */
        $page = Injector::inst()->get(HaveIBeenPwnedPage::class);

        /** @var HaveIBeenPwnedPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwnedPageController::class, false, [$page]);

        $this->assertInstanceOf(HaveIBeenPwnedPageController::class, $controller);
    }

    public function testCheckEmail()
    {
        /** @var HaveIBeenPwnedPage $page */
        $page = Injector::inst()->get(HaveIBeenPwnedPage::class);

        /** @var HaveIBeenPwnedPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwnedPageController::class, false, [$page]);

        // Log out. Solidly I hope
        Security::setCurrentUser(null);
        Injector::inst()->get(IdentityStore::class)->logOut();

        $response = $controller->checkEmail(null);

        // If there's no user, it should just return itself
        $this->assertInstanceOf(HaveIBeenPwnedPageController::class, $response);

        $member = Member::create(['Email' => 'test@test.com']);
        Security::setCurrentUser($member);
        $body = file_get_contents(__DIR__ . '/../fixtures/breachmails.json');
        // This sets up the mock client to respond to the request it gets
        // with an HTTP 200 containing your mock body.
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);

        /** @var HaveIBeenPwnedPageController $controller */
        $controller = Injector::inst()->get(HaveIBeenPwnedPageController::class, false, [$page]);

        /** @var HaveIBeenPwnedPageController $response */
        $response = $controller->checkEmail(null, ['handler' => $mock]);

        $this->assertContains('17Media', $response->Content);

        // We don't have a full set, so Yahoo shouldn't show
        $this->assertNotContains('Yahoo', $response->Content);
    }
}
