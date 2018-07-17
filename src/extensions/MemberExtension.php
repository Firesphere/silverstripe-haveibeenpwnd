<?php

namespace Firesphere\HaveIBeenPwnd\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;

/**
 * Class \Firesphere\HaveIBeenPwnd\Extensions\MemberExtension
 *
 * @property Member|MemberExtension $owner
 * @property int $PasswordIsPwnd
 * @property string $BreachedSites
 */
class MemberExtension extends DataExtension
{
    private static $db = [
        'PasswordIsPwnd' => 'Int',
        'BreachedSites'  => 'Text'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['BreachedSites', 'PasswordIsPwnd']);
        if ($this->owner->PasswordIsPwnd > 0) {
            $text = _t(static::class . '.PWNDHelp', 'If the error says that you "have been Pwnd", it means your password appears in the <a href="https://haveibeenpwned.com/Privacy">Have I Been Pwnd</a> database.
        Therefore, we can not accept your password, because it is insecure or known to have been breached.
        Before a password is safely stored in our database, we test if the password has been breached. We do not share your password.
        We run a safe test against the HaveIBeenPwnd database to. None of your data is shared or stored at HaveIBeenPwnd.
        For more information, you can read up on "Password safety", and we strongly recommend installing a password manager if you haven\'t already.
        Several options are LastPass, BitWarden and 1Password. These services are also able to test your passwords against the HaveIBeenPwnd database,
        to see if your passwords are secure and safe.<br />
        Furthermore, <a href="https://www.troyhunt.com/introducing-306-million-freely-downloadable-pwned-passwords/">Troy Hunt explains why and how this service is important</a>.');

            $fields->findOrMakeTab('Root.HaveIBeenPwnd', _t(static::class . '.PWNDTAB', 'Have I Been Pwnd?'));
            $help = LiteralField::create('Helptext', '<p>' . $text . '</p>');
            $fields->addFieldToTab('Root.HaveIBeenPwnd', $help);
        }

        if ($this->owner->BreachedSites) {
            $fields->findOrMakeTab('Root.HaveIBeenPwnd', _t(static::class . '.PWNDTAB', 'Have I Been Pwnd?'));
            $fields->addFieldToTab('Root.HaveIBeenPwnd',
                ReadonlyField::create('BreachedSites', _t(static::class . '.BREACHEDSITES', 'Breached sites')));
        }

        $fields->addFieldToTab('Root.Main', $countField = ReadonlyField::create('PasswordIsPwnd', 'Pwnd Count'));
        $countField->setDescription(_t(
            static::class . '.AMOUNT',
            'Amount of times the password appears in the Have I Been Pwnd database'
        ));
    }
}
