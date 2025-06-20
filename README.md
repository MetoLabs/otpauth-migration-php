

# MetoLabs\OtpAuthMigration

A simple PHP wrapper for the ```dim13/otpauth``` (https://github.com/dim13/otpauth) CLI tool to decode Google Authenticator migration URLs (```otpauth-migration://```) into usable TOTP secrets.

This package runs the ```otpauth``` CLI binary under the hood via [Symfony Process](https://symfony.com/doc/current/components/process.html) and parses its output.

---
## Features

- Validate and locate the ```otpauth``` binary automatically or accept a custom path
- Decode migration URLs (```otpauth-migration://offline?data=...```)
- Return detailed TOTP secrets including issuer, account, secret, algorithm, digits, and period

---
## Requirements

- PHP 8.0+
- ```dim13/otpauth``` CLI binary installed and accessible in your system ```PATH```
- Composer dependencies installed (```symfony/process```)

---
## Installation

1. Install the ```dim13/otpauth``` CLI tool (requires Go):

``` 
go install github.com/dim13/otpauth@latest
```

Alternatively, you can download a prebuilt binary from the [releases](https://github.com/dim13/otpauth/releases/latest) page.

Make sure ```otpauth``` is in your ```PATH``` when using installer.

2. Install the package via Composer:

```
composer require metolabs\otpauth-migration-php
```
3. Add the ```OtpAuthMigration``` class to your project or include via your autoloader.

---
## Usage

```
<?php

use MetoLabs\OtpAuthMigration\OtpAuthMigration;
use Symfony\Component\Process\Exception\ProcessFailedException;

require __DIR__.'/vendor/autoload.php';

// Your otpauth-migration URL (usually from a QR code or export)
$migrationUrl = 'otpauth-migration://offline?data=CjQKFKuOm4V945mpzWoDXUoJMyfferkVEg5qZG9lQGdtYWlsLmNvbRoGQW1hem9uIAEoATACEAIYASAA';

// Create the wrapper instance (auto-finds binary in $PATH)
$otp = OtpAuthMigration::make();

try {
    $accounts = $otp->decode($migrationUrl);

    foreach ($accounts as $account) {
        echo "Issuer:   {$account['issuer']}\n";
        echo "Account:  {$account['account']}\n";
        echo "Secret:   {$account['secret']}\n";
        echo "Algorithm: {$account['algorithm']}\n";
        echo "Digits:   {$account['digits']}\n";
        echo "Period:   {$account['period']}\n";
        echo str_repeat('-', 20) . "\n";
    }
} catch (ProcessFailedException $e) {
    echo 'Failed to decode migration URL: ', $e->getMessage(), '\n';
}
```
 ---
## Constructor options

```
// Create an instance
$otp = new OtpAuthMigration();
// Create an instance statically
$otp = OtpAuthMigration::make();

// You can specify a custom path to otpauth binary (optional)
$otp = new OtpAuthMigration('/usr/local/bin/otpauth');

// Same parameter can be passed with static instanciation
$otp = OtpAuthMigration::make('/usr/local/bin/otpauth');
```
 ---
## Passing environment variables

```
$env = [
    'PATH' => '/custom/path:' . getenv('PATH'),
];

$accounts = $otp->decode($migrationUrl, $env);
```
 
---

## 📄 License

MIT License

Copyright (c) 2025 MetoLabs

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

**THE SOFTWARE IS PROVIDED “AS IS”**, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

---
## Contributions

Contributions, issues, and feature requests are welcome!

---
## References

- ```dim13/otpauth``` GitHub (https://github.com/dim13/otpauth)
- Google Authenticator Key Migration Format (https://github.com/google/google-authenticator/wiki/Key-Uri-Format)
---