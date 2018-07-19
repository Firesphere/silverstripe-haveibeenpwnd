<?php

namespace Firesphere\HaveIBeenPwnd\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;

/**
 * Class HaveIBeenPwndService
 * @package Firesphere\HaveIBeenPwnd\Services
 */
class HaveIBeenPwndService
{
    use Configurable;

    /**
     * Api endpoint emails
     */
    const PWND_URL = 'https://haveibeenpwned.com/api/';

    /**
     * API endpoint passwords
     */
    const PWND_API_URL = 'https://api.pwnedpasswords.com/';

    /**
     * API Version
     */
    const API_VERSION = '2';

    /**
     * Useragent
     */
    const USER_AGENT = 'SilverStripe-Firesphere-HaveIBeenPwnd-checker/1.0';

    /**
     * @config
     * @var bool
     */
    private static $allow_pwnd = false;

    /**
     * @config
     * @var bool
     */
    private static $save_pwnd = true;

    /**
     * @var array
     */
    protected $args;

    /**
     * HaveIBeenPwndService constructor.
     * @param array $args
     */
    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * @param string $pwd
     * @return int
     * @throws GuzzleException
     */
    public function checkPwndPassword($pwd)
    {
        $this->args['base_uri'] = static::PWND_API_URL;

        $sha = sha1($pwd);
        $shaStart = substr($sha, 0, 5);
        $shaEnd = substr($sha, 5);
        /** @var Client $client */
        $client = Injector::inst()->createWithArgs(Client::class, [$this->args]);
        $result = $client->request('GET', 'range/' . $shaStart, [
            'headers' => [
                'user-agent'  => static::USER_AGENT,
                'api-version' => static::API_VERSION
            ]
        ]);

        return $this->checkList($result, $shaEnd);
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
        $suffixes = explode("\n", $result->getBody());
        foreach ($suffixes as $suffix) {
            list($suffix, $pwnCount) = explode(':', trim($suffix));
            if ($suffix === $shaEnd) {
                $count += (int)$pwnCount;
                break;
            }
        }

        return $count;
    }

    /**
     * @param Member $member
     * @return string
     * @throws GuzzleException
     */
    public function checkPwndEmail($member)
    {
        $this->args['base_uri'] = static::PWND_URL;
        $uniqueField = Member::config()->get('unique_identifier_field');
        $account = $member->{$uniqueField};

        /** @var Client $client */
        $client = Injector::inst()->createWithArgs(Client::class, [$this->args]);

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
     * @return string
     */
    private function checkBreaches($result)
    {
        $body = $result->getBody();

        $sites = [];

        $breaches = Convert::json2array($body);
        foreach ($breaches as $breach) {
            if (!empty($breach['Name'])) {
                $sites[] = $breach['Name'];
            }
        }

        if (count($sites)) {
            return implode(', ', $sites);
        }

        return '';
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }
}
