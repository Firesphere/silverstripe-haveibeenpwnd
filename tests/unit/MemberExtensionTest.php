<?php

namespace Firesphere\HaveIBeenPwned\Tests;

use Firesphere\HaveIBeenPwned\Extensions\MemberExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\Tab;
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
        $this->assertNull($fields->fieldByName('Root.HaveIBeenPwned'));
        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('PwndDisabled'));

        $this->member->BreachedSites = '000error, test';

        $fields = $this->member->getCMSFields();

        /** @var MemberExtension $extension */
        $extension = Injector::inst()->get(MemberExtension::class);
        $extension->setOwner($this->member);
        $extension->updateCMSFields($fields);

        $this->assertInstanceOf(Tab::class, $fields->fieldByName('Root.HaveIBeenPwned'));
        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('BreachedSites'));

        $this->assertContains('Known breaches', $fields->forTemplate());
        $this->assertContains('If the error says that you "have been Pwnd", ', $fields->forTemplate());
    }

    protected function setUp()
    {
        $this->member = Member::create([
            'Email'          => 'test@test.com',
            'PasswordIsPwnd' => 0,
            'Password'       => '1234567890', // I is good password
            'BreachedSites'  => ''
        ]);

        return parent::setUp();
    }
}
