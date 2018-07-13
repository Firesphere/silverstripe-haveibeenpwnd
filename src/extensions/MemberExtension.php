<?php

namespace Firesphere\HaveIBeenPwnd\Extensions;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

/**
 * Class \Firesphere\HaveIBeenPwnd\Extensions\MemberExtension
 *
 * @property Member|MemberExtension $owner
 * @property boolean $PasswordIsPwnd
 * @property string $BreachedSites
 */
class MemberExtension extends DataExtension
{
    use Configurable;

    const PWND_URL = 'https://haveibeenpwned.com/api/';

    const PWND_API_URL = 'https://api.pwnedpasswords.com/';

    const API_VERSION = '2';

    const USER_AGENT = 'SilverStripe-Firesphere-HaveIBeenPwnd-checker';

    private static $db = [
        'PasswordIsPwnd' => 'Boolean(false)',
        'BreachedSites'  => 'Text'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['BreachedSites']);
        if ($this->owner->PasswordIsPwnd) {
            $text = _t(static::class . 'PWNDHelp', '<b>Help, when I try to update a password, I get an error!</b><br />
        If the error says that you "have been Pwnd", it means your password appears in the <a href="https://haveibeenpwned.com/Privacy">Have I Been Pwnd</a> database.
        Therefore, we can not accept your password, because it is insecure or known to have been breached.<br />
        Before a password is safely stored in our database, we test if the password has been breached. We do not share your password.
        We run a safe test against the HaveIBeenPwnd database to. None of your data is shared or stored at HaveIBeenPwnd.
        For more information, you can read up on "Password safety", and we strongly recommend installing a password manager if you haven\'t already.<br />
        Several options are LastPass, BitWarden and 1Password. These services are also able to test your passwords against the HaveIBeenPwnd database,
        to see if your passwords are secure and safe.<br />
        Furthermore, <a href="https://www.troyhunt.com/introducing-306-million-freely-downloadable-pwned-passwords/">Troy Hunt explains why and how this service is important</a>.');

            $help = LiteralField::create('Helptext', '<p>' . $text . '</p>');
            $fields->addFieldToTab('Root.HaveIBeenPwnd', $help);
        }

        parent::updateCMSFields($fields);
    }

    /**
     * @param $pwd
     * @param ValidationResult $valid
     * @return void
     * @throws ValidationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function onBeforeChangePassword($pwd, $valid)
    {
        $allowBreach = static::config()->get('allow_pwnd');
        $breachCount = static::config()->get('pwn_treshold');
        $storeBreach = static::config()->get('save_pwnd');
        $breached = '';
        $isPwndCount = $this->checkPassword($pwd);

        if ($isPwndCount > 0) {
            usleep(1500); // We need to conform to the FUP, max 1 request per 1500ms
            // Always mark as Pwnd if it's true
            $this->owner->PasswordIsPwnd = true;
            $breached = $this->checkEmail($valid);
        }

        if ($breached !== '' && $storeBreach) {
            $this->owner->BreachedSites = $breached;
        }

        // Although it would be stupid, the pwnd treshold can be disabled
        // Or even allow for breached passwords. Not exactly ideal either
        if (!$allowBreach || ($isPwndCount > $breachCount && $breachCount !== 0)) {
            $valid->addError(_t(static::class . 'KNOWN', 'Your password appears in the Have I Been Pwnd database'));
            $valid->addError($breached);
            throw new ValidationException($valid, 255);
        }
    }

    /**
     * @param $pwd
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkPassword($pwd)
    {
        $sha = sha1($pwd);
        $shaStart = substr($sha, 0, 5);
        $shaEnd = substr($sha, 5);
        $client = new Client([
            'base_uri' => static::PWND_API_URL
        ]);
        $result = $client->request('GET', 'range/' . $shaStart, [
            'headers' => [
                'user-agent'  => static::USER_AGENT,
                'api-version' => static::API_VERSION
            ]
        ]);

        return $this->checkList($result, $shaEnd);
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkEmail()
    {
        $uniqueField = Member::config()->get('unique_identifier_field');
        $account = $this->owner->{$uniqueField};

        $client = new Client([
            'base_uri' => static::PWND_URL
        ]);

        $result = $client->request('GET', 'breachedaccount/' . $account . '?truncateResponse=true', [
            'headers' => [
                'user-agent'  => static::USER_AGENT,
                'api-version' => static::API_VERSION
            ]
        ]);

        return $this->checkBreaches($result);
    }

    /**
     * @param ResponseInterface $result
     * @param $shaEnd
     * @return int
     */
    private function checkList($result, $shaEnd)
    {
        $count = 0;
        $shaEnd = strtoupper($shaEnd);
        $suffixes = explode("\r\n", $result->getBody());
        foreach ($suffixes as $suffix) {
            list($suffix, $pwnCount) = explode(':', $suffix);
            if ($suffix === $shaEnd) {
                $count += (int)$pwnCount;
            }
        }

        return $count;
    }

    /**
     * @param ResponseInterface $result
     * @return string
     */
    private function checkBreaches($result)
    {
        $body = $result->getBody();

        $breakline = "\r\n";
        $sites = [];

        $message = _t(
            static::class . 'KNOWN_BREACH_PLUS_BREACHES',
            "To help you identify where you have been breached, your username or email address appears in the following breaches:$breakline"
        );
        $breaches = Convert::json2array($body);
        foreach ($breaches as $breach) {
            if (!empty($breach['Name'])) {
                $sites[] = $breach['Name'];
            }
        }

        return $message . ' ' . implode(', ', $sites);
    }
}
