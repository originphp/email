# Email

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/email.svg?branch=masterhttps://travis-ci.org/originphp/email.svg?branch=master)](https://travis-ci.org/originphp/email)
[![coverage](https://coveralls.io/repos/github/originphp/email/badge.svg?branch=master)](https://coveralls.io/github/originphp/email?branch=master)

The `Email` class enables you to send emails easily through SMTP.

## Installation

To install from the command line type

```linux
$ composer require originphp/email
```

## Email Configuration

When you create an instance of the Email object you will need to pass a configuration array.

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
```

The keys for the config are as follows:

- *host*: this is smtp server hostname
- *port*: port number default 25
- *username*: the username to access this SMTP server
- *password*: the password to access this SMTP server
- *ssl*: default is false, set to true if you want to connect via SSL
- *tls*: default is false, set to true if you want to enable TLS
- *timeout*: how many seconds to timeout
- *domain*: When we send the HELO command to the sever we have to identify your hostname, so we will use localhost or HTTP_SERVER var if client is not set.

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

The default email sending behavior is to send a HTML message with a text version, which is a best practice and reduces the risk of your email ending up in spam folders. 

### Send an Email

To send an email with both HTML and text versions:

```php
use Origin\Email\Email;
$Email = new Email($config);
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->htmlMessage('<p>This is the html content</p>')
    ->send();
```

### Sending HTML Only Email

To send a HTML only email, you need to tell the Email utility use the html format.

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

### Sending Text Email

To send a text email, you need to tell the Email utility use the text format.

```php
use Origin\Email\Email;
$Email = new Email();
$Email->to('somebody@originphp.com')
    ->from('me@originphp.com')
    ->subject('This is a test')
    ->textMessage('This is the text content')
    ->format('text')
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
    ->format('text')
    ->send();
```