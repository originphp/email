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
use \InvalidArgumentException;
use \BadMethodCallException;
use Origin\Email\Exception\SmtpException;
use Origin\Email\Message;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    public function testMessage()
    {
        $message = new Message('Header: value', 'Message body');
        $this->assertEquals('Header: value', $message->header());
        $this->assertEquals('Message body', $message->body());
        $expected = "Header: value\r\n\r\nMessage body";
        $this->assertEquals($expected, $message->message());
        $this->assertEquals($expected, (string) $message);
    }
}
