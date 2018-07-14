<?php

namespace Firesphere\HaveIBeenPwnd\Tests;

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
        $this->member = Member::create(['Email' => 'test@test.com', 'PasswordIsPwnd' => false]);
        return parent::setUp();
    }

    public function testUpdateCMSFields()
    {
        $fields = $this->member->getCMSFields();

        $this->assertNull($fields->dataFieldByName('HelpText'));

        $this->member->PasswordIsPwnd = true;
        $id = $this->member->write();
        $this->member = Member::get()->byID($id);
        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(LiteralField::class, $fields->dataFieldByName('HelpText'));
    }
}
