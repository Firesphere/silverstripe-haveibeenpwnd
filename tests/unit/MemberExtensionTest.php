<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\ReadonlyField;
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

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('PasswordIsPwnd'));
        $this->assertNull($fields->dataFieldByName('Helptext'));

        $this->member->BreachedSites = '000error, test';

        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('BreachedSites'));
    }
}
