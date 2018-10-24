[![Build Status](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-haveibeenpwnd/build-status/master)
[![codecov](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd/branch/master/graph/badge.svg)](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd)
[![Maintainability](https://api.codeclimate.com/v1/badges/bfc8d4c5de506318af0b/maintainability)](https://codeclimate.com/github/Firesphere/silverstripe-HaveIBeenPwned/maintainability)
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

## Only environment

To not make engineers life impossible and allow for using pwnd passwords on local environments in `dev` mode,
by default, the Pwnd service is turned off in `dev` mode

# Can I USe

Simply put? Sure. Admitted, this is Open Source software, and in theory, you can use it any way you want.

You can license this work, by buying a usage subscription. It will allow you to request support, or just supporting the developer.

### But it is licensed BSD!

Yes, it is and you can use it as such. I'd just appreciate a bit of help every now and then.

# Actual license

This module is published under BSD 3-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-3-Clause

Copyright (c) 2012-NOW(), Simon "Sphere" Erkelens

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


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
