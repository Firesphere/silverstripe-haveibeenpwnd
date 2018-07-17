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
        $page = HaveIBeenPwndPage::create(['AuthorID' => 1]);

        $member = Injector::inst()->get(DefaultAdminService::class)->findOrCreateDefaultAdmin();
        
        $this->assertTrue($page->canCreate($member));

        $page->write();

        $this->assertFalse($page->canCreate());

        $page->delete();
    }
}
