# Email

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/email.svg?branch=master)](https://travis-ci.org/originphp/email)
[![coverage](https://coveralls.io/repos/github/originphp/email/badge.svg?branch=master)](https://coveralls.io/github/originphp/email?branch=master)

The `Email` class enables you to send emails easily through SMTP.

## Installation

To install from the command line type

```linux
$ composer require originphp/email
```

## Email Configuration

In your bootstrap or application config. If you create a default account, then you do not need to specify an account or configure the instance of the email.

```php
Email::config('default',[
    'host' => 'smtp.example.com',
    'port' => 465,
    'username' => 'demo@example.com',
    'password' => 'secret',
    'timeout' => 5,
    'ssl' => true,
    'tls' => false
]);
```

The keys for the config are as follows:

- _host_: this is smtp server hostname
- _port_: port number default 25
- _username_: the username to access this SMTP server
- _password_: the password to access this SMTP server
- _ssl_: default is false, set to true if you want to connect via SSL
- _tls_: default is false, set to true if you want to enable TLS
- _timeout_: how many seconds to timeout
- _domain_: When we send the HELO command to the sever we have to identify your hostname, so we will use localhost or HTTP_SERVER var if client is not set.

You can also pass an array with configuration when you create an instance of the Email object.

```php
$config = [
    'host' => 'smtp.example.com',
    'port' => 25,
    'username' => 'demo@example.com',
    'password' => 'secret',
    'timeout' => 5,
    'ssl' => true,
    'tls' => false
]
$email = new Email($config);
```

You can also pass keys such as `from`,`to`,`cc`,`bcc`,`sender` and `replyTo` this pass the data to its functions either as string if its just an email or an array if you want to include a name. Remember if you are going to automatically cc or bcc somewhere, then you have to next call addBcc or addCc to ensure that you don't overwrite this.

For example

```php
[
    'from' => ['james@originphp.com' => 'James'],
    'replyTo' => 'no-reply@originphp.com'
    'bcc' => ['someone@origin.php' => 'Someone','another-person@example.com']
]
```

## Sending Emails

The default email sending behavior is to send a text version. However it best practice to send both HTML and text and this reduces the risk of your email ending up in spam folders.

When an email is sent it will return a Message object, if an error is encountered when sending then the email class will throw an exception which you can catch in try/catch block.

### Sending an Email (Text)

To send an email

```php
use Origin\Email\Email;
$Email = new Email($config);
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->send();
```

### Send both a HTML and Text Version (Recommend)

To send an email with both HTML and text versions:

```php
use Origin\Email\Email;
$Email = new Email($config);
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->htmlMessage('<p>This is the html content</p>')
    ->format('both')
    ->send();
```

### Sending HTML Only Email

To send a HTML only email, you need to tell the Email utility use the HTML format.

```php
use Origin\Email\Email;
$Email = new Email($config));
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->htmlMessage('<p>This is the html content</p>')
    ->format('html')
    ->send();
```

## Adding Attachments

To add attachments to an email message

```php
use Origin\Email\Email;
$Email = new Email($config);
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->addAttachment($filename1)
    ->addAttachment($filename2,'Logo.png')
    ->send();
```

## Using Multiple Accounts

If you have configured using `Email::config('gmail',$config)` then you can use it like this

```php
use Origin\Email\Email;
$Email = new Email('gmail');
```

Or

```php
use Origin\Email\Email;
$Email = new Email();
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->account('gmail')
    ->send();
```

## Oauth2

To configure your email account to use Oauth2 authentication, instead of providing a password
you can use a token.

```php
 Email::config('gsuite', [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'somebody@gmail.com',
    'token' => 'b1816172fd2ba98f3af520ef572e3a47', // see token generation below
    'ssl' => false,
    'tls' => true
]);
```

### Generating Tokens

To generate Oauth2 tokens, you can use the [thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client) package or if you are using Google (Gsuite/Gmail) then you can use the command line script provided. The script provided is only ideal for sending emails from your own account, rather than from a user account.

#### Google Command-Line OAuth Token Generator

The Google Client Library API allows you to generate tokens from the command line (without having to redirect to a script), and I have included a quick script for this.

To obtain an Oauth2 token that you can use with your Gsuite/Gmail account follow these instructions.

1. Enable the Gsuite API for your email account by going to [https://developers.google.com/gmail/api/quickstart/php](https://developers.google.com/gmail/api/quickstart/php) and then click on `Enable the Gmail API` button, then the `Download Client Configuration` button. Once you have done this, save the data file to `data/credentials.json` in the `vendor/originphp/email/` folder.

2. Install Google Client Library (PHP), by running the following command:

```linux
$ composer require google/apiclient:^2.0
```

3. Run the Google CLI script.

```linux
$ vendor/bin/google
```

Now copy the URL into your browser and follow the instructions on screen, and this will provide you with a code. Paste the code into your console window, and your token will be displayed on the screen. The token JSON will be saved to `data/token.json` for future reference.

4. Add the token that was generated to your email configuration.