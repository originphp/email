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

use Origin\Email\Email;
use \Exception;
use \InvalidArgumentException;

trait TestTrait
{
    public function callMethod(string $method, array $args = [])
    {
        if (empty($args)) {
            return $this->$method();
        }

        return call_user_func_array([$this, $method], $args);
    }

    public function getProperty(string $property)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }
    }

    public function setProperty(string $property, $value)
    {
        $this->$property = $value;
    }
}

class MockEmail extends Email
{
    use TestTrait;
    protected $boundary = '0000000000000000000000000000';
    public static $backup = [];
    
    public static function backup()
    {
        static::$backup = static::$config;
    }
    /**
     * Reset config for testing
     */
    public static function reset()
    {
        static::$config = static::$backup;
    }
}

class EmailTest extends \PHPUnit\Framework\TestCase
{
    private function directory()
    {
        return dirname(__DIR__);
    }

    public function testTo()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->to('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('to');
        $this->assertEquals(['james@originphp.com', null], $property[0]);

        $email = $email->to('james@originphp.com', 'James');
        $property = $email->getProperty('to');
        $this->assertEquals(['james@originphp.com', 'James'], $property[0]);
    }
    public function testFrom()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->from('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('from');

        $this->assertEquals(['james@originphp.com', null], $property);

        $email = $email->from('james@originphp.com', 'James');
        $property = $email->getProperty('from');
        $this->assertEquals(['james@originphp.com', 'James'], $property);
    }

    public function testSender()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->sender('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('sender');
        $this->assertEquals(['james@originphp.com', null], $property);

        $email = $email->sender('james@originphp.com', 'James');
        $property = $email->getProperty('sender');
        $this->assertEquals(['james@originphp.com', 'James'], $property);
    }

    public function testReplyTo()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->replyTo('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('replyTo');
        $this->assertEquals(['james@originphp.com', null], $property);

        $email = $email->replyTo('james@originphp.com', 'James');
        $property = $email->getProperty('replyTo');
        $this->assertEquals(['james@originphp.com', 'James'], $property);
    }

    public function testReturnPath()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->returnPath('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('returnPath');
        $this->assertEquals(['james@originphp.com', null], $property);

        $email = $email->returnPath('james@originphp.com', 'James');
        $property = $email->getProperty('returnPath');
        $this->assertEquals(['james@originphp.com', 'James'], $property);
    }

    public function testBcc()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->bcc('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('bcc');
        $this->assertEquals(['james@originphp.com', null], $property[0]);

        $email = $email->bcc('james@originphp.com', 'James');
        $property = $email->getProperty('bcc');
        $this->assertEquals(['james@originphp.com', 'James'], $property[0]);
    }

    public function testCc()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->cc('james@originphp.com');
        $this->assertInstanceOf(Email::class, $email);
        $property = $email->getProperty('cc');
        $this->assertEquals(['james@originphp.com', null], $property[0]);

        $email = $email->cc('james@originphp.com', 'James');
        $property = $email->getProperty('cc');
        $this->assertEquals(['james@originphp.com', 'James'], $property[0]);
    }

    /**
     * @depends testTo
     */
    public function testAddTo()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->to('james@originphp.com', 'James');
        $email = $email->addTo('guest@originphp.com', 'Guest');
        $property = $email->getProperty('to');
        $this->assertEquals(['guest@originphp.com', 'Guest'], $property[1]);
    }

    /**
     * @depends testCc
     */
    public function testAddCc()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->cc('james@originphp.com', 'James');
        $email = $email->addCc('guest@originphp.com', 'Guest');
        $property = $email->getProperty('cc');
        $this->assertEquals(['guest@originphp.com', 'Guest'], $property[1]);
    }

    /**
     * @depends testBcc
     */
    public function testAddBcc()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->bcc('james@originphp.com', 'James');
        $email = $email->addBcc('guest@originphp.com', 'Guest');
        $property = $email->getProperty('bcc');
        $this->assertEquals(['guest@originphp.com', 'Guest'], $property[1]);
    }

    public function testCcDefault()
    {
        $email = new MockEmail([
            'cc' => ['james@originphp.com','guest@originphp.com' => 'Guest'],
        ]);
      
        $property = $email->getProperty('cc');
        $this->assertEquals(['james@originphp.com', null], $property[0]);
        $this->assertEquals(['guest@originphp.com', 'Guest'], $property[1]);
    }

    public function testBccDefault()
    {
        $email = new MockEmail([
            'bcc' => ['james@originphp.com','guest@originphp.com' => 'Guest'],
        ]);
      
        $property = $email->getProperty('bcc');
        $this->assertEquals(['james@originphp.com', null], $property[0]);
        $this->assertEquals(['guest@originphp.com', 'Guest'], $property[1]);
    }

    public function testSubject()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->subject('A subject line');
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('A subject line', $email->getProperty('subject'));
    }

    public function testTextMessage()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->textMessage('Text message.');
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('Text message.', $email->getProperty('textMessage'));
    }

    public function testHtmlMessage()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->htmlMessage('<p>Html message.</p>');
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('<p>Html message.</p>', $email->getProperty('htmlMessage'));
    }

  

    public function testAddHeader()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->addHeader('X-mailer', 'OriginPHP');
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals(['X-mailer' => 'OriginPHP'], $email->getProperty('additionalHeaders'));
    }

    public function testAddAttachment()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->addAttachment($this->directory() . '/phpunit.xml.dist');
        $this->assertInstanceOf(Email::class, $email);

        $email->addAttachment($this->directory() . '/README.md', 'Important.md');

        $expected = [
            $this->directory() . '/phpunit.xml.dist' => 'phpunit.xml.dist',
            $this->directory() . '/README.md' => 'Important.md',
        ];
        $this->assertSame($expected, $email->getProperty('attachments'));

        $this->expectException(Exception::class);
        $email->addAttachment('/users/tony_stark/floor_plan.pdf');
    }

    /**
     * @depends testAddAttachment
     */
    public function testAddAttachments()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email = $email->addAttachments([
            $this->directory() . '/phpunit.xml.dist',
            $this->directory() . '/README.md' => 'Important.md',
        ]);
        $this->assertInstanceOf(Email::class, $email);

        $expected = [
            $this->directory() .  '/phpunit.xml.dist' => 'phpunit.xml.dist',
            $this->directory() . '/README.md' => 'Important.md',
        ];

        $this->assertSame($expected, $email->getProperty('attachments'));
    }

    public function testBuildMessageHeaderCore()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #1')
            ->format('text')
            ->textMessage('this is a test');

        $headers = $email->callMethod('buildHeaders');

        $this->assertEquals('1.0', $headers['MIME-Version']);
        $this->assertEquals(date('r'), $headers['Date']);
        $validUUID = (bool)preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}@originphp.com$/i', $headers['Message-ID']);
        $this->assertTrue($validUUID);
        $this->assertEquals('test #1', $headers['Subject']);
        $this->assertEquals('mailer@originphp.com', $headers['From']);
        $this->assertEquals('james@originphp.com', $headers['To']);
        $this->assertEquals('text/plain; charset="UTF-8"', $headers['Content-Type']);

        // Adjust data
        $email->to('james@originphp.com', 'James')
            ->from('mailer@originphp.com', 'OriginPHP Mailer');
        $headers = $email->callMethod('buildHeaders');

        $this->assertEquals('OriginPHP Mailer <mailer@originphp.com>', $headers['From']);
        $this->assertEquals('James <james@originphp.com>', $headers['To']);
    }

    public function testBuildMessageHeaderOptional()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #2')
            ->textMessage('this is a test');

        $email->addHeader('X-mailer', 'OriginPHP');

        $email->bcc('guest1@originphp.com');
        $email->addBcc('guest2@originphp.com', 'Guest 2');

        $email->cc('guest3@originphp.com');
        $email->addCc('guest4@originphp.com', 'Guest 4');

        $headers = $email->callMethod('buildHeaders');
        $this->assertEquals('OriginPHP', $headers['X-mailer']);
        $this->assertEquals('guest1@originphp.com, Guest 2 <guest2@originphp.com>', $headers['Bcc']);
        $this->assertEquals('guest3@originphp.com, Guest 4 <guest4@originphp.com>', $headers['Cc']);
    }

    public function testBuildMessageHeaderEncoding()
    {
        // Check subject and names of headers are encoded
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('ragnar@originphp.com', 'Ragnarr Loþbrók')
            ->from('mailer@originphp.com')
            ->subject('Valhöll')
            ->textMessage('this is a test')
            ->format('text');
        $headers = $email->callMethod('buildHeaders');

        $this->assertEquals('=?UTF-8?B?VmFsaMO2bGw=?=', $headers['Subject']);
        $this->assertEquals('Ragnarr =?UTF-8?B?TG/DvmJyw7Nr?= <ragnar@originphp.com>', $headers['To']);
        $this->assertFalse(isset($headers['Content-Transfer-Encoding'])); // Dont encode message

        // If we have UTF8 chars in message we need the header ContentTransferEncoding
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #2')
            ->textMessage('Are you in Valhöll?')
            ->format('text');

        $headers = $email->callMethod('buildHeaders');
        $this->assertEquals('quoted-printable', $headers['Content-Transfer-Encoding']);
    }

    public function testBuildMessageHeaderContentType()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #1')
            ->textMessage('this is a test')
            ->format('text');

        $headers = $email->callMethod('buildHeaders');

        $this->assertEquals('text/plain; charset="UTF-8"', $headers['Content-Type']);

        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #1')
            ->htmlMessage('<p>this is a test</p>')
            ->format('html');

        $headers = $email->callMethod('buildHeaders');

        $this->assertEquals('text/html; charset="UTF-8"', $headers['Content-Type']);

        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('test #1')
            ->textMessage('this is a test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('both');

        $headers = $email->callMethod('buildHeaders');

        $boundary = $email->getProperty('boundary');
        $this->assertEquals("multipart/alternative; boundary=\"{$boundary}\"", $headers['Content-Type']);
    }

    public function testCreateMessageText()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->format('text');
        $result = $this->messageToString($email->callMethod('buildMessage'));
        $this->assertEquals("this is a test\r\n", $result);
    }

    public function testCreateMessageTextAttachments()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->format('text');

        $tempfile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempfile, 'foo/bar');

        $email->addAttachment($tempfile, 'test.txt');
        $headers = $email->callMethod('buildHeaders');
        $expected = 'multipart/mixed; boundary="0000000000000000000000000000"';
        $this->assertEquals($expected, $headers['Content-Type']);
        $result = $this->messageToString($email->callMethod('buildMessage'));

        $expected = "--0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\nthis is a test\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/plain; name=\"test.txt\"\r\nContent-Disposition: attachment\r\nContent-Transfer-Encoding: base64\r\n\r\nZm9vL2Jhcg==\r\n\r\n\r\n--0000000000000000000000000000--";
        $this->assertEquals($expected, $result);
    }

    public function testCreateMessageHtml()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('html test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('html');
        $result = $this->messageToString($email->callMethod('buildMessage'));
        $this->assertEquals("<p>this is a test</p>\r\n", $result);
    }

    public function testCreateMessageHtmlAttachments()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('html');

        $tempfile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempfile, 'foo/bar');

        $email->addAttachment($tempfile, 'test.txt');
        $headers = $email->callMethod('buildHeaders');
        $expected = 'multipart/mixed; boundary="0000000000000000000000000000"';
        $this->assertEquals($expected, $headers['Content-Type']);
        $result = $this->messageToString($email->callMethod('buildMessage'));

        $expected = "--0000000000000000000000000000\r\nContent-Type: text/html; charset=\"UTF-8\"\r\n\r\n<p>this is a test</p>\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/plain; name=\"test.txt\"\r\nContent-Disposition: attachment\r\nContent-Transfer-Encoding: base64\r\n\r\nZm9vL2Jhcg==\r\n\r\n\r\n--0000000000000000000000000000--";
        $this->assertEquals($expected, $result);
    }

    public function testCreateMessageBoth()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('both');

        $result = $this->messageToString($email->callMethod('buildMessage'));

        $expected = "--0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\nthis is a test\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/html; charset=\"UTF-8\"\r\n\r\n<p>this is a test</p>\r\n\r\n--0000000000000000000000000000--";

        $this->assertEquals($expected, $result);

        // Check Encoding is added when needed
        $email->to('ragnar@originphp.com')
            ->textMessage('Are you in Valhöll?')
            ->htmlMessage('<p>Are you in Valhöll?</p>')
            ->format('both');

        $result = $this->messageToString($email->callMethod('buildMessage'));
        //pr(str_replace("\r\n", '\r\n', $result));
        $expected = "--0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nAre you in Valh=C3=B6ll?\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/html; charset=\"UTF-8\"\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n<p>Are you in Valh=C3=B6ll?</p>\r\n\r\n--0000000000000000000000000000--";

        $this->assertEquals($expected, $result);
    }

    public function testCreateMessageBothAttachments()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('both');


        $tempfile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempfile, 'foo/bar');

        $email->addAttachment($tempfile, 'test.txt');
        $headers = $email->callMethod('buildHeaders');
        $expected = 'multipart/mixed; boundary="0000000000000000000000000000"';
        $this->assertEquals($expected, $headers['Content-Type']);
        $result = $this->messageToString($email->callMethod('buildMessage'));

        $expected = "--0000000000000000000000000000\r\nContent-Type: multipart/alternative; boundary=\"alt-0000000000000000000000000000\"\r\n\r\n--alt-0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\nthis is a test\r\n\r\n--alt-0000000000000000000000000000\r\nContent-Type: text/html; charset=\"UTF-8\"\r\n\r\n<p>this is a test</p>\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/plain; name=\"test.txt\"\r\nContent-Disposition: attachment\r\nContent-Transfer-Encoding: base64\r\n\r\nZm9vL2Jhcg==\r\n\r\n\r\n--0000000000000000000000000000--";
        $this->assertEquals($expected, $result);
    }

    /**
     * Check that multiple attachments work properly
     */
    public function testMultipleAttachments()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->format('text');

        // Needs a unique filename
        $tempfile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempfile, 'foo/bar');
        $email->addAttachment($tempfile, 'test1.txt');

        $tempfile = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($tempfile, 'foo/bar');
        $email->addAttachment($tempfile, 'test2.txt');

        $headers = $email->callMethod('buildHeaders');
        $expected = 'multipart/mixed; boundary="0000000000000000000000000000"';
        $this->assertEquals($expected, $headers['Content-Type']);
        $result = $this->messageToString($email->callMethod('buildMessage'));

        $expected = "--0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\nthis is a test\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/plain; name=\"test1.txt\"\r\nContent-Disposition: attachment\r\nContent-Transfer-Encoding: base64\r\n\r\nZm9vL2Jhcg==\r\n\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/plain; name=\"test2.txt\"\r\nContent-Disposition: attachment\r\nContent-Transfer-Encoding: base64\r\n\r\nZm9vL2Jhcg==\r\n\r\n\r\n--0000000000000000000000000000--";
        $this->assertEquals($expected, $result);
    }

    /**
     * Use this whilst creating or debugging tests to see output
     *
     * @param [type] $result
     * @return void
     */
    protected function debugResult($result)
    {
        pr($result);
        pr(str_replace("\r\n", '\r\n', $result));
    }

    /**
     * To help with testing
     *
     * @param array $result
     * @return void
     */
    protected function messageToString($result)
    {
        return implode("\r\n", $result);
    }


    public function testApplyConfig()
    {
        $config = ['to' => 'to@example.com', 'from' => 'from@example.com', 'sender' => 'sender@example.com', 'bcc' => 'bcc@example.com', 'cc' => 'cc@example.com', 'replyTo' => 'replyTo@example.com'];
      
        $email = new MockEmail($config);
  
        $this->assertEquals(['to@example.com',null], $email->getProperty('to')[0]);
        $this->assertEquals(['from@example.com',null], $email->getProperty('from'));
        $this->assertEquals(['bcc@example.com',null], $email->getProperty('bcc')[0]);
        $this->assertEquals(['cc@example.com',null], $email->getProperty('cc')[0]);
        $this->assertEquals(['sender@example.com',null], $email->getProperty('sender'));
        $this->assertEquals(['replyTo@example.com',null], $email->getProperty('replyTo'));
    }

    public function testSendNoHtmlMessageException()
    {
        $this->expectException(Exception::class);
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('html test');
        $email->send();
    }

    public function testSendNoTextMessageException()
    {
        $this->expectException(Exception::class);
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->format('text');
        $email->send();
    }

    public function testSendWithoutSmtp()
    {
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com')
            ->from('mailer@originphp.com')
            ->subject('text test')
            ->textMessage('this is a test')
            ->htmlMessage('<p>this is a test</p>')
            ->format('both');

        $result = $email->send()->message();
        $expected = "--0000000000000000000000000000\r\nContent-Type: text/plain; charset=\"UTF-8\"\r\n\r\nthis is a test\r\n\r\n--0000000000000000000000000000\r\nContent-Type: text/html; charset=\"UTF-8\"\r\n\r\n<p>this is a test</p>\r\n\r\n--0000000000000000000000000000--";
        $this->assertStringContainsString($expected, $result);
    }
   

    public function testSendNoFromAddress()
    {
        $this->expectException(Exception::class);
        $email = new Email(['engine'=>'Test']);
        $email->to('james@originphp.com')
            ->subject('simple test')
            ->format('text')
            ->textMessage('not really imporant');
        $email->send();
    }
    public function testSendNoToAddress()
    {
        $this->expectException(Exception::class);
        $email = new Email(['engine'=>'Test']);
        $email->from('james@originphp.com')
        ->subject('simple test')
        ->format('text')
        ->textMessage('not really imporant');
        $email->send();
    }


    public function testSendTextMessageNotSet()
    {
        $this->expectException(Exception::class);
        $email = new Email(['engine'=>'Test']);
        $email->from('james@originphp.com')
            ->subject('exception testt')
            ->format('text');
        $email->send();
    }

    public function testSendHtmlMessageNotSet()
    {
        $this->expectException(Exception::class);
        $email = new Email(['engine'=>'Test']);
        $email->from('james@originphp.com')
            ->subject('exception test')
            ->format('html');
        $email->send();
    }

    public function testSmtpLog()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $this->assertIsArray($email->smtpLog());
    }
    /**
     * to test from the command line
     *  GMAIL_USERNAME=username@gmail.com GMAIL_PASSWORD=secret phpunit TestCase/Utility/EmailTest.php
     *
     * @return void
     */
    public function testSmtpSend()
    {
        if (! $this->env('GMAIL_USERNAME') or ! $this->env('GMAIL_PASSWORD')) {
            $this->markTestSkipped(
                'GMAIL username and password not setup'
            );
        }
       
        $email = new Email($this->getConfig());
        $email->to($this->env('GMAIL_USERNAME'))
            ->subject('PHPUnit Test: ' . date('Y-m-d H:i:s'))
            ->from($this->env('GMAIL_USERNAME'), 'PHP Unit')
            ->format('both')
            ->htmlMessage('<p>This is an email test to ensure that the framework can send emails properly and can include this in code coverage.<p>')
            ->textMessage('This is an email test to ensure that the framework can send emails properly and can include this in code coverage.');

        $result = $email->send();
        $this->assertNotEmpty($result);
        
        sleep(1); // small delay before next email is sent
        return implode("'\n", $email->smtpLog());
    }

    /**
     * @depends testSmtpSend
     */
    public function testCheckSmtpLog(string $log)
    {
        $this->assertStringContainsString('EHLO [192.168.1.7]', $log);
        $this->assertStringContainsString('MAIL FROM: <'.$this->env('GMAIL_USERNAME').'>', $log);
        $this->assertStringContainsString('RCPT TO: <'.$this->env('GMAIL_USERNAME').'>', $log);
    }

    /**
     * to test from the command line
     *  GMAIL_USERNAME=username@gmail.com GMAIL_PASSWORD=secret phpunit TestCase/Utility/EmailTest.php
     *
     * @return void
     */
    public function testSmtpSendTLS()
    {
        if (! $this->env('GMAIL_USERNAME') or ! $this->env('GMAIL_PASSWORD')) {
            $this->markTestSkipped(
                'GMAIL username and password not setup'
            );
        }
        $config = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => $this->env('GMAIL_USERNAME'),
            'password' => $this->env('GMAIL_PASSWORD'),
            'ssl' => false,
            'tls' => true,
            'domain' => '[192.168.1.7]',
        ];

        $email = new Email($config);
        $email->to($this->env('GMAIL_USERNAME'))
            ->subject('PHPUnit Test: ' . date('Y-m-d H:i:s') .' [TLS]')
            ->from($this->env('GMAIL_USERNAME'), 'PHP Unit')
            ->format('both')
            ->htmlMessage('<p>This is an email test to ensure that the framework can send emails properly with TLS and can include this in code coverage.<p>')
            ->textMessage('This is an email test to ensure that the framework can send emails properly with TLS and can include this in code coverage.');

        $this->assertNotEmpty($email->send());
    }

    public function testErrorConnectingToServer()
    {
        $this->expectException(Exception::class);

        if (! $this->env('GMAIL_USERNAME') or ! $this->env('GMAIL_PASSWORD')) {
            $this->markTestSkipped(
                'GMAIL username and password not setup'
            );
        }
        $config = [
            'host' => 'invalid.originphp.com',
            'port' => 25,
            'username' => 'username',
            'password' => 'password',
            'ssl' => false,
            'tls' => false,
            'domain' => '[192.168.1.7]',
        ];

        $email = new Email($config);
        $email->to($this->env('GMAIL_USERNAME'))
            ->subject('PHPUnit Test: ' . date('Y-m-d H:i:s'))
            ->from($this->env('GMAIL_USERNAME'), 'PHP Unit')
            ->format('both')
            ->htmlMessage('<p>This is a test email</p>');

        $result = $email->send();
    }

    /**
     * Ensure that the Email part is fine
     *
     * @return void
     */
    public function testEmailValidationAgainstHeaderInjection()
    {
        $email = new MockEmail(['engine'=>'Test']);
        $this->expectException(Exception::class);
        $email->callMethod('validateEmail', ["mailer@originphp.com\nOmg: injected"]);
    }

    protected function getConfig()
    {
        return [
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'username' => $this->env('GMAIL_USERNAME'),
            'password' => $this->env('GMAIL_PASSWORD'),
            'ssl' => true,
            'tls' => false,
            'domain' => '[192.168.1.7]',
        ];
    }


    public function testInvalidFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $email = new MockEmail(['engine' => 'Test']);
        $email->format('java');
    }

    public function testBuildHeaderOptionals()
    {
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com', 'James')
            ->from('mailer@originphp.com')
            ->subject("Injection test\nBcc: apollo@boxers.io");

        $optionals = ['sender' => 'Sender', 'replyTo' => 'Reply-To', 'returnPath' => 'Return-Path'];
        $email->sender('sender@originphp.com')
            ->replyTo('replyTo@originphp.com')
            ->returnPath('returnPath@originphp.com')
            ->format('text')->textMessage("Yo Adrian!\nRocky");
        $result = $email->send()->header();
    
        $this->assertStringContainsString('Sender: sender@originphp.com', $result);
        $this->assertStringContainsString('Reply-To: replyTo@originphp.com', $result);
        $this->assertStringContainsString('Return-Path: returnPath@originphp.com', $result);
    }

    public function testEmailHeaderInjectionAttack()
    {
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com', 'James')
            ->from('mailer@originphp.com')
            ->subject("Injection test\nBcc: apollo@boxers.io")
            ->format('text')->textMessage("Yo Adrian!\nRocky");
        $result = $email->send()->message();
        $this->assertStringContainsString('Subject: Injection =?UTF-8?B?dGVzdApCY2M6IGFwb2xsb0Bib3hlcnMuaW8=?=', $result);
        $this->assertStringNotContainsString('apollo@boxers.io', $result);
        
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com', "James\nBcc: apollo@boxers.io")
            ->from('mailer@originphp.com')
            ->subject('Injection test');
        $this->assertStringContainsString('To: James <james@originphp.com>', $result);
        $this->assertStringNotContainsString('apollo@boxers.io', $result);

        $this->expectException(Exception::class);
        $email = new MockEmail(['engine' => 'Test']);
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com', 'James')
            ->from('mailer@originphp.com')
            ->subject('Injection test')
            ->format('text')->textMessage("Yo Adrian!\nRocky");
        $email->addHeader('X-mailer', "Custom Mailer\nBcc: hacker@blackbox.io");
        $email->send();
    }

    public function testAttachmentAndEncoding()
    {
        $filename = $this->directory() . '/phpunit.xml.dist';
        $email = new MockEmail(['engine' => 'Test']);
        $email->to('james@originphp.com', 'James')
            ->from('mailer@originphp.com')
            ->subject('Viking: Ragnarr Loþbrók')
            ->htmlMessage('The email message has non-ascii chars Ragnarr Loþbrók.')
            ->format('html') // use HTML or TEXT only message. Not both
            ->addAttachment($filename);
        $result = $email->send()->message();
        $this->assertStringContainsString('Content-Type: text/html; charset="UTF-8"', $result);
        $this->assertStringContainsString('Content-Transfer-Encoding: quoted-printable', $result);
        $this->assertStringContainsString('The email message has non-ascii chars Ragnarr Lo=C3=BEbr=C3=B3k', $result);
    }


    protected function env(string $key)
    {
        return $_ENV[$key] ?? null;
    }
}
