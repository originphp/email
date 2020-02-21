<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Origin\Test\Mailer;

use \Exception;
use Origin\Email\Email;
use \BadMethodCallException;
use \InvalidArgumentException;
use Origin\Email\Exception\SmtpException;

class Oauth2Test extends \PHPUnit\Framework\TestCase
{
    public function testGmail()
    {
        if (! $this->env('GSUITE_TOKEN') or ! $this->env('GSUITE_USERNAME')) {
            $this->markTestSkipped('No credentials');
        }
        Email::config('test-gmail', [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => $this->env('GSUITE_USERNAME'),
            'token' => $this->env('GSUITE_TOKEN') ,
            'ssl' => false,
            'tls' => true,
            'domain' => '[192.168.1.7]',
        ]);
        
        $email = new Email();
        $email->to('info@originphp.com')
            ->subject('PHPUnit Test: ' . date('Y-m-d H:i:s') . ' [GSUITE]')
            ->from($this->env('GSUITE_USERNAME'), 'PHP Unit')
            ->format('html')
            ->account('test-gmail')
            ->htmlMessage('<p>This is an email test to ensure that the framework can send emails properly with TLS and can include this in code coverage.<p>');
        $this->assertNotEmpty($email->send());
    }


    public function testOffice365()
    {
        $this->expectException(SmtpException::class);

        $path = dirname(__DIR__, 1);
        Email::config('test-office365', [
            'host' => 'smtp.office365.com',
            'port' => 587,
            'username' =>  'info@originphp.com',
            'token' => '123456789',
            'ssl' => false,
            'tls' => true,
            'domain' => '[192.168.1.7]',
        ]);

        $email = new Email();
        $email->to('info@originphp.com')
            ->subject('PHPUnit Test: ' . date('Y-m-d H:i:s') . ' [GSUITE]')
            ->from('info@originphp.com', 'PHP Unit')
            ->format('html')
            ->account('test-office365')
            ->htmlMessage('<p>This is an email test to ensure that the framework can send emails properly with TLS and can include this in code coverage.<p>');
        $email->send();
    }

    /**
    * Work with ENV vars
    *
    * @param string $key
    * @return mixed
    */
    protected function env(string $key)
    {
        $result = getenv($key);

        return $result ? $result : null;
    }
}
