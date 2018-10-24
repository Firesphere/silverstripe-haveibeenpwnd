<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 14-Jul-18
 * Time: 11:38
 */

namespace Firesphere\HaveIBeenPwned\Tests;

use Firesphere\HaveIBeenPwned\Controllers\HaveIBeenPwnedPageController;
use Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\DefaultAdminService;
use SilverStripe\Security\Security;

class HaveIBeenPwnedPageTest extends SapphireTest
{
    public function testGetControllerName()
    {
        /** @var HaveIBeenPwnedPage $page */
        $page = HaveIBeenPwnedPage::create();

        $this->assertEquals(HaveIBeenPwnedPageController::class, $page->getControllerName());
    }

    public function testCanCreate()
    {
        $member = Injector::inst()->get(DefaultAdminService::class)->findOrCreateDefaultAdmin();
        Security::setCurrentUser($member);

        if (HaveIBeenPwnedPage::get()->count()) {
            HaveIBeenPwnedPage::get()->removeAll();
        }

        $page = HaveIBeenPwnedPage::create();

        $this->assertTrue($page->canCreate($member));

        $page->write();

        $this->assertFalse($page->canCreate());

        $page->delete();
    }
}
