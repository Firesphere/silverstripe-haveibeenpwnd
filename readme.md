[![CircleCI](https://img.shields.io/circleci/project/github/Firesphere/silverstripe-haveibeenpwnd.svg)](https://circleci.com/gh/Firesphere/silverstripe-haveibeenpwnd)
[![codecov](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd/branch/master/graph/badge.svg)](https://codecov.io/gh/Firesphere/silverstripe-haveibeenpwnd)
[![Maintainability](https://api.codeclimate.com/v1/badges/bfc8d4c5de506318af0b/maintainability)](https://codeclimate.com/github/Firesphere/silverstripe-haveibeenpwnd/maintainability)

# _**WARNING**_

This module is not a replacement for two factor authentication, nor will it improve security for existing users that don't change their passwords.

This module is only to make sure no known passwords are re-used.

Although it adds to the user security te enforce unique passwords, this module will not prevent any password leaks like the ones used by HaveIBeenPwnd.

# Have I Been Pwnd for SilverStripe

This module checks on password change, if the SHA1 of the password appears in the Have I Been Pwnd database.

Only a count of the amount of times the password shows in the database is collected and stored.

# Installation

`composer require firesphere/haveibeenpwnd`

# Configuration

```yaml

---
name: MyPwnConfig
    after: HaveIBeenPwnd
---
Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension:
  allow_pwnd: false
  save_pwnd: false
  pwn_treshold: 10

```

## Parameters

`allow_pwnd` If set to true, passwords that appear in the Have I been Pwnd database will be allowed

`save_pwnd` If set to true, all the breaches in which the user's username or email address appears

`pwn_treshold` If set to 0, a user may use the password despite it being breached. If set to another number, the password may be used if the amount of breaches is lower than the treshold.
This parameter is ignored if `allow_pwnd` is set to false

## Applying the validator extension to other PasswordValidators

Add the following to either of your config yml files. (Suggested is using an `extensions.yml` file)

```yaml

MyVendor\MyNameSpace\MyPasswordValidator:
  extensions:
    - Firesphere\HaveIBeenPwnd\Extensions\PasswordValidatorExtension

```

Replacing the vendor\namespace\validator with your own Validator namespace and classname

# Actual license

This module is published under BSD 3-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-2-Clause

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
