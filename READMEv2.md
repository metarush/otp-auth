Note: This documentation is applicable for `v3` only.
For older documentation, refer to `READMEv(n).md`.

---

# metarush/otp-auth

Authenticate and log in your users using one-time passwords (OTP) via email.

## Install

Install via composer as `metarush/otp-auth`

## Database setup

In addition to your usual `username` and/or `email` column, create the ff. columns in your users table.

- `otpHash` TEXT (255)
- `otpToken` TEXT(12)
- `otpExpire` INT (10)
- `rememberHash` TEXT (255)
- `rememberToken` TEXT (12)

Note: You can use other column names if you want, just set them in the config.

## Sample usage

The ff. example is meant to demonstrate the library's functionality in a simple way. It is not required to use this implementation as it is.

### _init.php

Create a `_init.php` file to be included on top of your script and put the ff.

**Initialize builder**

```php
$builder = new MetaRush\OtpAuth\Builder;
```

**Define SMTP servers**

```php
$smtpServers = [
    0 => $builder->SmtpServer()
        ->setHost('host')
        ->setUser('user')
        ->setPass('pass')
        ->setPort('465')
        ->setEncr('TLS'),
    1 => $builder->SmtpServer()
        ->setHost('host2')
        ->setUser('user')
        ->setPass('pass')
        ->setPort('465')
        ->setEncr('TLS'),
    ];
```

You can add as many as you like, the lib will failover to each automatically.

**Initialize the library with minimum config**

```php
$auth = $builder->setDsn('mysql:host=localhost;dbname=foo')
    ->setServers($smtpServers)
    ->setAdminEmails(['admin@example.com'])
    ->setAppName('foo')
    ->setFromEmail('noreply@example.com')
    ->setUsernameColumn('email')
    ->setNotificationFromEmail('noreply@example.com')
    ->setTable('users')
    ->build();
```

**Auto-login if username is remembered via cookie**

```php
if (!$auth->authenticated()) {
    $rememberedUsername = $auth->rememberedUsername();
    if (null !== $rememberedUsername) {
        $auth->login([
            'username' => $rememberedUsername
        ]);
    }
}
```

### login.php

Create a `login.php` file and put the ff.

```php
<?php

include '_init.php';

if ($_POST) {
    $username = $_POST['email'];

    // check if username exists
    if (!$auth->userExist($username))
        exit('User does not exist');

    // remember username for next page (otp.php)
    setcookie('username', $username);

    // send OTP to user's email
    $otp = $auth->generateToken(5);
    $auth->sendOtp($otp, $username);

    // redirect to OTP page
    header('location: otp.php');
}

?>

<?php if ($auth->authenticated()): ?>

    You are already logged-in

<?php else: ?>

    <form method="post">
        Email: <input type="text" name="email" />
    </form>

<?php endif; ?>
```

### otp.php

Create a `otp.php` file and put the ff.

```php
<?php

include '_init.php';

if ($_POST) {
    $otp = $_POST['otp'];
    $username = $_COOKIE['username'];

    // remember username in browser if user wants to
    if (isset($_POST['remember']))
        $auth->remember($username);

    // check if OTP is valid
    if (!$auth->validOtp($otp, $username))
        exit('Invalid OTP');

    // login username
    $auth->login([
        'username' => $username
    ]);

    echo 'OTP is valid';
    // redirect to your restricted page
}

?>

<?php if ($auth->authenticated()): ?>

    You are already logged-in

<?php else: ?>

    <form method="post">
        OTP: <input type="text" name="otp" />
        <br />
        <br />
        Remember? <input type="checkbox" name="remember" />
        <br />
        <br />
        <input type="submit" />
    </form>

<?php endif; ?>
```

### logout.php

Create a `logout.php` file and put the ff.

```php
<?php

include '_init.php';

// destroy user session
$auth->logout();
```

## Config methods

You can use the ff. methods in the builder object, before the `->build();` method

#### `setAdminEmails(array);`

Array of admin emails that will get error notifications

#### `setAppName(string);`

Label of your app on error notifications

#### `setBody(string);`

Body of the OTP email. If you set this, you must include the template var `{OTP}`.

Default: `{OTP}\r\n\r\nNote: This OTP is valid for 5 minutes`

#### `setCharacterPool(string);`

Pool of characters where the token will be derived from

Default: `0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`

#### `setCookiePrefix(string);`

Cookie name prefix used by `metarush/otp-auth`

Default: `MROA_`

#### `setCookiePath(?string);`

Cookie path used by `metarush/otp-auth`

Default: `/`

#### `setDbPass(string);`

DB password of your users table

#### `setDbUser(string);`

DB username of your users table

#### `setDsn(string);`

PDO DSN used to connect to your users table

#### `setEmailColumn(string);`

Table column where users' email is stored

Default: `email`

#### `setFromEmail(string);`

From Email of the OTP message

#### `setFromName(string);`

From Name of the OTP message

#### `setNotificationFromEmail(string);`

If you set an admin email via `setAdminEmail()`, you must set a From Email for error notifications

#### `setOtpExpire(int);`

OTP expiration in minutes. If you set this, make sure to also change the email message via `setBody(string);` to reflect the OTP expiration the email.

Default: `5`

#### `setOtpExpireColumn(string);`

Table column where OTP expire is stored

Default: `otpExpire`

#### `setOtpHashColumn(string);`

Table column where OTP hash is stored

Default: `otpHash`

#### `setOtpTokenColumn(string);`

Table column where OTP token is stored

Default: `otpToken`

#### `setRememberCookieExpire(int);`

How long "remember me" cookie expires in seconds

Default: `2592000` (30 days)

#### `setRememberHashColumn(string);`

Table column name for "remember me" hash

Default: `rememberHash`

#### `setRememberTokenColumn(string);`

Table column name for lookup token for "remember me" cookie

Default: `rememberToken`

#### `setServers(array);`

Array of `SmtpServer` objects. See above sample "Define SMTP servers"

#### `setSubject(string);`

Subject of the OTP email

Default: `Here's your OTP`

#### `setTable(string);`

Table where usernames will be authenticated

Default: `users`

#### `setUsernameColumn(string);`

Table column name where username is stored

Default: `username`


### SMTP round-robin mode

You can use round-robin mode to distribute the load to all SMTP hosts when sending OTP email.

To enable round-robin mode, you must use a storage driver to track the last server used to send email.

Available drivers and their config:

#### files

```php
$driver = 'files';
$driverConfig = [
    'path' => '/var/www/example/emailFallbackCache/'
];
```

#### memcached

```php
$driver = 'memcached';
$driverConfig = [
    'host'         => '127.0.0.1',
    'port'         => 11211,
    'saslUser'     => '',
    'saslPassword' => ''
];
```

Note: Only single server/non-distriubuted memcached is supported at the moment.

#### redis

```php
$driver = 'redis';
$driverConfig = [
    'host'      => '127.0.0.1',
    'port'      => 6379,
    'password'  => '',
    'database'  => 0
];
```

Note: Use memcached or redis if available as files is not recommended for heavy usage.

After selecting a driver, set the following in the builder object, before the `->build();` method:

```php
->setRoundRobinMode(true)
->setRoundRobinDriver($driver)
->setRoundRobinDriverConfig($driverConfig)
```

## Service methods

#### `authenticated(): bool`

Check if user is authenticated

#### `generateToken(int $length): string`

`$length` Length of token you want to generate.

Generate random token

#### `login(?array $userData = []): void`

Log in user as authenticated

`$userData` Optional arbitrary user data defined by you, e.g., userId, email

#### `logout(): void`

Log out user and remove "remember me" cookie

#### `remember(string $username, int $howLong = null): void`

Remember username's login in browser

`$username` Username to remember

`$howLong` How long to remember user in seconds. Default is value is 30 days unless `setRememberCookieExpire()` was used in config.

#### `rememberedUsername(?string $cookie = null): ?string`

Get remembered username (via cookie) if any.

`$cookie` If null, default cookie will be used.

#### `sendOtp(string $otp, string $username, bool $useNextSmtpHost = false, int $testLastServerKey = null): void`

Send OTP to user via email

`$otp` The OTP to send to user. You can use `generateToken()` service method to generate random OTP. Recommended OTP length is at least 8.

`$username` Username to send OTP to

`$useNextSmtpHost` Set to `true` on your next usage of `sendOtp()` if you want to use the next SMTP host available relative to the current user. This is useful if the last email is slow to arrive. E.g., Create a "try again" UI then use `sendOtp($otp, $username, true)` to send a new OTP using the next SMTP host.

#### `userData(): array`

Returns arbitrary user data, if set via login() param

#### `userExist(string $username): bool`

Check if user exist

`$username` Username to check

#### `validOtp(string $otp, string $username, ?string $testOtpToken = null): bool`

Validate OTP

`$otp` OTP to be validated

`$username` Username associated with the OTP

## Brute-force protection

We recommended using the [metarush/firewall](https://github.com/metarush/firewall) library for login brute-force protection.