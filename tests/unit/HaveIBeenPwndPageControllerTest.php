<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class HaveIBeenPwndPageControllerTest extends SapphireTest
{

    public function testClassExists()
    {
        $page = Injector::inst()->get(HaveIBeenPwndPage::class);

        $controller = Injector::inst()->get(HaveIBeenPwndPageController::class, $page);

        $this->assertInstanceOf(HaveIBeenPwndPageController::class, $controller);
    }
}