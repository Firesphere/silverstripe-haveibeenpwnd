<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use Firesphere\HaveIBeenPwnd\Extensions\MemberExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\Member;

class MemberExtensionTest extends SapphireTest
{
    /**
     * @var Member
     */
    protected $member;

    protected function setUp()
    {
        $this->member = Member::create(['Email' => 'test@test.com', 'PasswordIsPwnd' => false, 'Password' => uniqid('_Firesphere\\HaveIBeenPwnd\\Tests_', true)]);
        return parent::setUp();
    }

    public function testUpdateCMSFields()
    {
        $fields = $this->member->getCMSFields();

        $this->assertNull($fields->dataFieldByName('Helptext'));

    }
}
