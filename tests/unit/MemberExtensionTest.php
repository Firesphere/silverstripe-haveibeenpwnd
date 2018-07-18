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

    public function testUpdateCMSFields()
    {
        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('PasswordIsPwnd'));
        $this->assertNotContains('If the error says that you "have been Pwnd", ', $fields->forTemplate());

        $this->member->BreachedSites = '000error, test';

        $fields = $this->member->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('BreachedSites'));
        $this->assertTrue($fields->hasTabSet('HaveIBeenPwnd'));

        $this->assertContains('Breached sites', $fields->forTemplate());
    }

    protected function setUp()
    {
        $this->member = Member::create([
            'Email'          => 'test@test.com',
            'PasswordIsPwnd' => 0,
            'Password'       => '1234567890' // I is good password
        ]);

        return parent::setUp();
    }
}
