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
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\DefaultAdminService;
use SilverStripe\Security\Security;

class HaveIBeenPwndPageTest extends SapphireTest
{
    public function testGetControllerName()
    {
        /** @var HaveIBeenPwndPage $page */
        $page = HaveIBeenPwndPage::create();

        $this->assertEquals(HaveIBeenPwndPageController::class, $page->getControllerName());
    }

    public function testCanCreate()
    {
        $member = Injector::inst()->get(DefaultAdminService::class)->findOrCreateDefaultAdmin();
        Security::setCurrentUser($member);

        if (HaveIBeenPwndPage::get()->count()) {
            HaveIBeenPwndPage::get()->removeAll();
        }

        $page = HaveIBeenPwndPage::create();

        $this->assertTrue($page->canCreate($member));

        $page->write();

        $this->assertFalse($page->canCreate());

        $page->delete();
    }
}
