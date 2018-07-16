<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Security;

if (class_exists(HaveIBeenPwndPageController::class)) {

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

            $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, $page);

            // Log out. Solidly I hope
            Security::setCurrentUser(null);
            Injector::inst()->get(IdentityStore::class)->logOut();

            $response = $controller->checkEmail();

            // If there's no user, it should just return itself
            $this->assertInstanceOf(HaveIBeenPwndPageController::class, $response);
        }
    }
}
