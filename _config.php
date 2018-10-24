<?php

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordValidator;

if (!Injector::inst()->has(PasswordValidator::class)) {
    $validator = Injector::inst()->get(PasswordValidator::class);
    Member::set_password_validator($validator);
}
