<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 14-Jul-18
 * Time: 11:38
 */

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage;
use SilverStripe\Dev\SapphireTest;

class HaveIBeenPwndPageTest extends SapphireTest
{
    public function testGetControllerName()
    {
        /** @var HaveIBeenPwndPage $page */
        $page = HaveIBeenPwndPage::create();

        $this->assertEquals(HaveIBeenPwndPageController::class, $page->getControllerName());
    }
}
