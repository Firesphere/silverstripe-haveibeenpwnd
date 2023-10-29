[![Build Status](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/build-status/master)
[![codecov](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd/branch/master/graph/badge.svg)](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd)
[![Maintainability](https://api.codeclimate.com/v1/badges/9d11289b0376bbd8356b/maintainability)](https://codeclimate.com/github/Firesphere/silverstripe-haveibeenpwnd/maintainability)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

# _**WARNING**_

This module is not a replacement for two factor authentication.

Users are _actively_ locked out if their password is found to be pwnd and forced to reset their password.

Although it adds to the user security te enforce unique passwords, this module will not prevent any password leaks like the ones used by HaveIBeenPwned.

# Have I Been Pwned for SilverStripe

This module checks on password change and login, if the SHA1 of the password appears in the Have I Been Pwnd database.

There is _never_ a full password transmitted to HaveIBeenPwned, only the first 5 characters of a SHA1 of the password, the response
from HaveIBeenPwned is then compared locally.

Given the nature of HaveIBeenPwned, even if a password is intercepted (the connection is HTTPS and Troy Hunt is not the easiest
when it comes to security), the password has already been out in the wild, so this scenario is a very unlikely one to cause more breaches.

Only a count of the amount of times the password shows in the database is collected, next to which known breaches contain the users Email or Username.
This information about the password and the email are unrelated. HaveIBeenPwned does _not_ provide a relation between the two. On purpose.

# Requirements

SilverStripe Framework 4.x
GuzzleHttp 6.x
PHP 5.6+

# Installation

`composer require firesphere/HaveIBeenPwnd`

# Configuration


Making calls to the Have I Been Pwned API requires a key. There's [a full blog post on why here](https://www.troyhunt.com/authentication-and-the-have-i-been-pwned-api).

To configure this module to use the key, define an environment variable on your server or your .env:

```dotenv
HIBP_API_KEY="MYAPIKEY1234567898765431"
```

Other configurations can be done in YML:
```yaml

---
Name: MyPwnConfig
    after: HaveIBeenPwnedConfig
---
Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService:
  allow_pwnd: false
  save_pwnd: true
---
Only:
  environment: dev
---
Firesphere\HaveIBeenPwned\Services\HaveIBeenPwnedService:
  allow_pwnd: true
```

## Parameters

`allow_pwnd` If set to true, passwords that appear in the Have I been Pwnd database will be allowed

`save_pwnd` If set to true, all the breaches in which the user's username or email address appears

## Applying the validator extension to other PasswordValidators

Add the following to either of your config yml files. (Suggested is using an `extensions.yml` file)

```yaml

MyVendor\MyNameSpace\MyPasswordValidator:
  extensions:
    - Firesphere\HaveIBeenPwned\Extensions\PasswordValidatorExtension

```

Replacing the vendor\namespace\validator with your own Validator namespace and classname

By default, if the default PasswordValidator isn't registered, this module will register it
to make sure the changes are applied. You can override this in your own `_config.php` file.

## Only environment

To not make engineers life impossible and allow for using pwnd passwords on local environments in `dev` mode,
by default, the Pwnd service is turned off in `dev` mode

# Can I USe

Simply put? Sure. Admitted, this is Open Source software, and in theory, you can use it any way you want.

You can license this work, by buying a usage subscription. It will allow you to request support, or just supporting the developer.

# Did you read this entire readme? You rock!

Pictured below is a cow, just for you.
```

               /( ,,,,, )\
              _\,;;;;;;;,/_
           .-"; ;;;;;;;;; ;"-.
           '.__/`_ / \ _`\__.'
              | (')| |(') |
              | .--' '--. |
              |/ o     o \|
              |           |
             / \ _..=.._ / \
            /:. '._____.'   \
           ;::'    / \      .;
           |     _|_ _|_   ::|
         .-|     '==o=='    '|-.
        /  |  . /       \    |  \
        |  | ::|         |   | .|
        |  (  ')         (.  )::|
        |: |   |;  U U  ;|:: | `|
        |' |   | \ U U / |'  |  |
        ##V|   |_/`"""`\_|   |V##
           ##V##         ##V##
```
