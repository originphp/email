<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);
namespace Origin\Email;

use \Exception;
use \BadMethodCallException;
use \InvalidArgumentException;
use Origin\Email\Exception\SmtpException;
use Origin\Configurable\StaticConfigurable as Configurable;

class Email
{
    use Configurable;

    const CRLF = "\r\n";

    protected $to = [];

    protected $from = [];

    protected $cc = [];

    protected $bcc = [];

    protected $replyTo = [];

    protected $sender = [];

    protected $returnPath = [];

    protected $subject = null;

    protected $charset = 'UTF-8';

    protected $htmlMessage = null;

    protected $textMessage = null;

    protected $headers = [];

    protected $messageId = null;

    protected $boundary = null;

    protected $attachments = [];

    protected $additionalHeaders = [];

    protected $encodeMessage = false;

    /**
     * This will be set automatically, e.g quoted-printable, base etc
     *
     * @var string
     */
    protected $encoding = null;

    /**
     * Email account config to use for sending email through this instance
     *
     * @var array
     */
    protected $account = null;

    protected $socket = null;
    /**
     * Holds the log for SMTP
     *
     * @var array
     */
    protected $smtpLog = [];

    /**
     * Email format.
     * The best practice is send both HTML and text
     *
     * @var string
     */
    protected $emailFormat = 'text';

    /**
     * Message object
     *
     * @var \Origin\Email\Message
     */
    protected $message = null;

    /**
     * Vars to be loaded in template
     *
     * @var array
     */
    protected $viewVars = [];

    /**
     * Constructor
     *
     * @param string|array $config name of account or an array with the following option keys
     *   - host: hostname e.g smtp.gmail.com
     *   - port: default 25
     *   - username
     *   - password
     *   - tls: default false.
     *   - ssl: default false
     *   - domain: The FQDN of the server that is sending the email, if it is not from server then use [192.0.2.1]
     *   - timeout: default 30 seconds
     *   - engine: Smtp. You can use test, then email is not sent
     */
    public function __construct($config = 'default')
    {
        if (extension_loaded('mbstring') === false) {
            throw new Exception('mbstring extension is not loaded');
        }

        mb_internal_encoding($this->charset); // mb_list_encodings()

        if (is_string($config)) {
            $config = static::config($config);
        }

        if ($config) {
            $this->setAccount($config);
        }
    }

    /**
     *
     */
    private function setAccount(array $config): void
    {
        $defaults = [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'tls' => false,
            'ssl' => false,
            'domain' => null,
            'timeout' => 30,
            'engine' => 'Smtp'
        ];
        $this->account = array_merge($defaults, $config);
        $this->applyConfig();
    }

    /**
     * Sets and gets the email account to be used by this email instance.
     *
     * @param string|array $config
     * @return \Origin\Email\Email|array
     */
    public function account(string $config = null)
    {
        if ($config === null) {
            return $this->account;
        }
        $account = $config;
        $config = static::config($config);

        if (! $config) {
            throw new InvalidArgumentException(sprintf('The email account `%s` does not exist.', $account));
        }
        $this->setAccount($config);

        return $this;
    }

    /**
     * Goes through the account config and passing data to certain function. When
     * setting up email in app for an account setting from all over the app can be
     * messy.
     *
     * @return void
     */
    protected function applyConfig(): void
    {
        foreach (['to', 'from', 'sender', 'replyTo'] as $method) {
            if (isset($this->account[$method])) {
                foreach ((array) $this->account[$method] as $email => $name) {
                    if (is_int($email)) {
                        $email = $name;
                        $name = null;
                    }
                    call_user_func_array([$this, $method], [$email,$name]);
                }
            }
        }
        if (isset($this->account['bcc'])) {
            foreach ((array) $this->account['bcc'] as $email => $name) {
                if (is_int($email)) {
                    $email = $name;
                    $name = null;
                }
                $this->addBcc($email, $name);
            }
        }

        if (isset($this->account['cc'])) {
            foreach ((array) $this->account['cc'] as $email => $name) {
                if (is_int($email)) {
                    $email = $name;
                    $name = null;
                }
                $this->addCc($email, $name);
            }
        }
    }

    /**
     * To
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function to(string $email, string $name = null): Email
    {
        $this->setEmail('to', $email, $name);

        return $this;
    }

    /**
     * Add another to address
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function addTo(string $email, string $name = null): Email
    {
        $this->addEmail('to', $email, $name);

        return $this;
    }

    /**
     * Set a cc address
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function cc(string $email, string $name = null): Email
    {
        $this->setEmail('cc', $email, $name);

        return $this;
    }

    /**
     * Add another cc address
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function addCc(string $email, string $name = null): Email
    {
        $this->addEmail('cc', $email, $name);

        return $this;
    }

    /**
     * Sets the bcc
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function bcc(string $email, string $name = null): Email
    {
        $this->setEmail('bcc', $email, $name);

        return $this;
    }

    /**
     * Add another bcc address
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function addBcc(string $email, string $name = null): Email
    {
        $this->addEmail('bcc', $email, $name);

        return $this;
    }

    /**
     * Sets the email from
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function from(string $email, string $name = null): Email
    {
        $this->setEmailSingle('from', $email, $name);

        return $this;
    }

    /**
     * Sets the sender for the email
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function sender(string $email, string $name = null): Email
    {
        $this->setEmailSingle('sender', $email, $name);

        return $this;
    }

    /**
     * Sets the reply-to
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function replyTo(string $email, string $name = null): Email
    {
        $this->setEmailSingle('replyTo', $email, $name);

        return $this;
    }

    /**
     * Sets the return path
     *
     * @param string $email
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function returnPath(string $email, string $name = null): Email
    {
        $this->setEmailSingle('returnPath', $email, $name);

        return $this;
    }

    /**
     * Sets the email subject
     *
     * @param string $subject
     * @return \Origin\Email\Email
     */
    public function subject(string $subject): Email
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Sets the text version of email
     *
     * @param string $message
     * @return \Origin\Email\Email
     */
    public function textMessage(string $message): Email
    {
        $this->textMessage = $message;

        return $this;
    }

    /**
     * Sets the html version of email
     *
     * @param string $message
     * @return \Origin\Email\Email
     */
    public function htmlMessage(string $message): Email
    {
        $this->htmlMessage = $message;

        return $this;
    }

    /**
     * Sets the charset for the email message
     *
     * @param string $encoding
     * @return \Origin\Email\Email
     */
    public function charset(string $encoding): Email
    {
        $this->charset = $encoding;

        return $this;
    }
    /**
     * Add a custom header to the email message
     *
     * @param string $name
     * @param string $value
     * @return \Origin\Email\Email
     */
    public function addHeader(string $name, string $value): Email
    {
        $this->additionalHeaders[$name] = $value;

        return $this;
    }

    /**
     * Adds an attachment
     *
     * @param string $filename
     * @param string $name
     * @return \Origin\Email\Email
     */
    public function addAttachment(string $filename, string $name = null): Email
    {
        if ($name == null) {
            $name = basename($filename);
        }
        if (file_exists($filename)) {
            $this->attachments[$filename] = $name;

            return $this;
        }
        throw new InvalidArgumentException($filename . ' not found');
    }

    /**
     * Adds multiple attachments
     *
     * @param array $attachments ['/tmp/filename','/images/logo.png'=>'Your Logo.png']
     * @return \Origin\Email\Email
     */
    public function addAttachments(array $attachments): Email
    {
        foreach ($attachments as $filename => $name) {
            if (is_int($filename)) {
                $filename = $name;
                $name = null;
            }
            $this->addAttachment($filename, $name);
        }

        return $this;
    }

    /**
     * Sends the email
     *
     * @param bool $debug set to true to not send via SMTP
     * @return \Origin\Email\Message
     */
    public function send(bool $debug = false): Message
    {
        if (empty($this->from)) {
            throw new BadMethodCallException('From email is not set.');
        }

        if (empty($this->to)) {
            throw new BadMethodCallException('To email is not set.');
        }

        if (($this->format() === 'html' || $this->format() === 'both') && empty($this->htmlMessage)) {
            throw new BadMethodCallException('Html Message not set.');
        }

        if (($this->format() === 'text' || $this->format() === 'both') && empty($this->textMessage)) {
            throw new BadMethodCallException('Text Message not set.');
        }

        $this->message = $this->render();

        if ($this->account === null && $debug === false) {
            throw new BadMethodCallException('No Email Account Configured.');
        }

        if ($debug === true || $this->account['engine'] === 'Test') {
            return $this->message;
        }

        $this->smtpSend();

        return $this->message;
    }

    /**
     * Builds the headers and message
     *
     * @return \Origin\Email\Message
     */
    protected function render(): Message
    {
        $headers = '';
        foreach ($this->buildHeaders() as $header => $value) {
            $headers .= "{$header}: {$value}" . self::CRLF;
        }
        $message = implode(self::CRLF, $this->buildMessage());

        return new Message($headers, $message);
    }

    /**
     * Sends the message through SMTP
     *
     * @return void
     */
    protected function smtpSend(): void
    {
        $account = $this->account;

        $this->openSocket($account);
        defer($context, 'fclose', $this->socket);

        $this->connect($account);

        $this->authenticate($account);

        $this->sendCommand('MAIL FROM: <' . $this->from[0] . '>', '250');
        $recipients = array_merge($this->to, $this->cc, $this->bcc);
        foreach ($recipients as $recipient) {
            $this->sendCommand('RCPT TO: <' . $recipient[0] . '>', '250|251');
        }

        $this->sendCommand('DATA', '354');

        $this->sendCommand($this->message . self::CRLF . self::CRLF . self::CRLF . '.', '250');

        $this->sendCommand('QUIT', '221');
    }

    /**
     * Handles the STMP authentication
     *
     * @see https://developers.google.com/google-apps/gmail/xoauth2_protocol#the_sasl_xoauth2_mechanism
     * @see https://developers.google.com/gmail/imap/xoauth2-protocol
     * @param array $account
     * @return void
     */
    protected function authenticate(array $account) : void
    {
        if (isset($account['username']) && isset($account['password'])) {
            $this->sendCommand('AUTH LOGIN', '334');
            $this->sendCommand(base64_encode($account['username']), '334');
            $this->sendCommand(base64_encode($account['password']), '235');
        } elseif (isset($account['username']) && isset($account['token'])) {
            $param = "user={$account['username']}\001auth=Bearer ". $account['token'] ."\001\001";
            try {
                $this->sendCommand('AUTH XOAUTH2 ' . base64_encode($param), '235');
            } catch (SmtpException $ex) {
                // @important Gsuite states to send empty response to get error. Using RSET, works with
                // both office365 and gsuite.
                $this->sendCommand('RSET');
            }
        }
    }

    /**
     * Connects to the SMTP server
     *
     * @param array $account
     * @return void
     */
    protected function connect(array $account): void
    {
        $this->sendCommand(null, '220');

        /**
         * The argument field contains the fully-qualified domain name of the SMTP client if one is available.
         * In situations in which the SMTP client system does not have a meaningful domain name (e.g., when its
         * address is dynamically allocated and no reverse mapping record is available), the client SHOULD send
         * an address literal (see section 4.1.3), optionally followed by information that will help to identify
         * the client system. Address literal is [192.0.2.1]
         * @see http://www.ietf.org/rfc/rfc2821.txt
         */
        $domain = '[127.0.0.1]';
        if (isset($account['domain'])) {
            $domain = $account['domain'];
        }

        $this->sendCommand("EHLO {$domain}", '250');
        if ($account['tls']) {
            $this->sendCommand('STARTTLS', '220');
            // stream_socket can return bool or int
            if (stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new SmtpException('The server did not accept the TLS connection.');
            }
            $this->sendCommand("EHLO {$domain}", '250');
        }
    }

    /**
     * Checks if the socket is opened
     *
     * @return boolean
     */
    protected function isConnected(): bool
    {
        return is_resource($this->socket);
    }

    /**
     * Sends a command to the socket and waits for a response.
     *
     * @param string|null $data
     * @param string $code
     * @return string $code
     */
    protected function sendCommand(string $data = null, string $code = '250'): string
    {
        if ($data != null) {
            $this->socketLog($data);
            fputs($this->socket, $data . self::CRLF);
        }
        $response = '';
        $startTime = time();
        while (is_resource($this->socket) && ! feof($this->socket)) {
            $buffer = @fgets($this->socket, 515);
            if (! $buffer) {
                break; // no more data or an error has occured
            }
            $this->socketLog(rtrim($buffer));
            $response .= $buffer;

            /**
             * RFC5321 S4.1 + S4.2
             * @see https://tools.ietf.org/html/rfc5321
             * Stop if 4 character is space or response is only 3 characters (not valid must be handled
             * according to standard)
             */

            if (substr($buffer, 3, 1) === ' ' || strlen($buffer) === 3) {
                break;
            }

            $info = stream_get_meta_data($this->socket);
            if ($info['timed_out'] || (time() - $startTime) >= $this->account['timeout']) {
                throw new SmtpException('SMTP timeout.');
            }
        }

        $responseLines = explode(self::CRLF, rtrim($response, self::CRLF));
        $lastResponse = end($responseLines);

        if (preg_match("/^($code)/", $lastResponse)) {
            return $code; // Return response code
        }

        throw new SmtpException(sprintf('SMTP Error: %s', $response));
    }

    /**
     * Adds message to the SMTP log
     *
     * @param string $data
     * @return void
     */
    protected function socketLog(string $data): void
    {
        $this->smtpLog[] = $data;
    }

    /**
     * Opens a Socket or throws an exception
     *
     * @param array $account
     * @return void
     */
    protected function openSocket(array $account, array $options = []): void
    {
        $protocol = $account['ssl'] ? 'ssl' : 'tcp';

        $server = $protocol . '://' . $account['host'] . ':' . $account['port'];
        $this->socketLog('Connecting to ' . $server);

        set_error_handler([$this, 'connectionErrorHandler']);
        $this->socket = stream_socket_client(
            $server,
            $errorNumber,
            $errorString,
            $account['timeout'],
            STREAM_CLIENT_CONNECT,
            stream_context_create($options)
        );
        restore_error_handler();

        if (! $this->isConnected()) {
            $this->socketLog('Unable to connect to the SMTP server.');
            throw new SmtpException('Unable to connect to the SMTP server.');
        }
        $this->socketLog('Connected to SMTP server.');

        /**
         * Does not time out just an array returned by stream_get_meta_data() with the key timed_out
         */
        stream_set_timeout($this->socket, $this->account['timeout']); // Sets a timeouted key
    }

    /**
     * This is the error handler when opening the stream
     *
     * @param int $code
     * @param string $message
     * @return void
     */
    protected function connectionErrorHandler(int $code, string $message): void
    {
        $this->smtpLog[] = $message;
    }

    /**
     * Returns the smtp log
     *
     * @return array
     */
    public function smtpLog(): array
    {
        return $this->smtpLog;
    }

    protected function setEmail(string $var, string $email = null, string $name = null): void
    {
        $this->$var = [];
        $this->addEmail($var, $email, $name);
    }

    protected function addEmail(string $var, string $email = null, string $name = null): void
    {
        $this->validateEmail($email);
        $this->$var[] = [$email, $name];
    }

    protected function setEmailSingle(string $var, string $email = null, string $name = null): void
    {
        $this->validateEmail($email);
        $this->$var = [$email, $name];
    }

    /**
     * Validates an email
     *
     * @internal this validation process also checks for newlines which is important in email header injection attacks
     * @param string $email
     * @return bool
     * @throws Exception
     */
    protected function validateEmail($email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        throw new InvalidArgumentException(sprintf('Invalid email address %s', $email));
    }

    /**
     * Validates a header line to prevent Email Header Injections
     *
     * @param string $input
     * @return bool
     */
    protected function validateHeader(string $input = null): bool
    {
        return ($input === str_ireplace(["\r", "\n", '%0A', '%0D'], '', $input));
    }

    /**
     * Builds an array of headers for the email
     *
     * @return array
     */
    protected function buildHeaders(): array
    {
        $headers = [];

        $headers['MIME-Version'] = '1.0';
        $headers['Date'] = date('r'); //RFC 2822
        $headers['Message-ID'] = $this->getMessageId();

        foreach ($this->additionalHeaders as $header => $value) {
            $headers[$header] = $value;
        }

        $headers['Subject'] = $this->getSubject();

        $optionals = ['sender' => 'Sender', 'replyTo' => 'Reply-To', 'returnPath' => 'Return-Path'];
        foreach ($optionals as $var => $header) {
            if ($this->$var) {
                $headers[$header] = $this->formatAddress($this->$var);
            }
        }

        $headers['From'] = $this->formatAddress($this->from);

        foreach (['to', 'cc', 'bcc'] as $var) {
            if ($this->$var) {
                $headers[ucfirst($var)] = $this->formatAddresses($this->$var);
            }
        }

        /**
         * Look for Email Header Injection
         */
        foreach ($headers as $header) {
            if (! $this->validateHeader($header)) {
                throw new InvalidArgumentException(sprintf('Possible Email Header Injection `%s`', $header));
            }
        }

        $headers['Content-Type'] = $this->getContentType();
        if ($this->needsEncoding() && empty($this->attachments) && $this->format() !== 'both') {
            $headers['Content-Transfer-Encoding'] = 'quoted-printable';
        }

        return $headers;
    }

    /**
     * Builds the message array
     *
     * @return array message
     */
    protected function buildMessage(): array
    {
        $message = [];

        $emailFormat = $this->format();
        $needsEncoding = $this->needsEncoding();
        $altBoundary = $boundary = $this->getBoundary();

        if ($this->attachments && ($emailFormat === 'html' || $emailFormat === 'text')) {
            $message[] = '--' . $boundary;
            if ($emailFormat === 'text') {
                $message[] = 'Content-Type: text/plain; charset="' . $this->charset . '"';
            }
            if ($emailFormat === 'html') {
                $message[] = 'Content-Type: text/html; charset="' . $this->charset . '"';
            }

            if ($needsEncoding) {
                $message[] = 'Content-Transfer-Encoding: quoted-printable';
            }
            $message[] = '';
        }

        if ($this->attachments && $emailFormat === 'both') {
            $altBoundary = 'alt-' . $boundary;
            $message[] = '--' . $boundary;
            $message[] = 'Content-Type: multipart/alternative; boundary="' . $altBoundary . '"';
            $message[] = '';
        }

        if (($emailFormat === 'text' || $emailFormat === 'both') && $this->textMessage) {
            if ($emailFormat === 'both') {
                $message[] = '--' . $altBoundary;
                $message[] = 'Content-Type: text/plain; charset="' . $this->charset . '"';
                if ($needsEncoding) {
                    $message[] = 'Content-Transfer-Encoding: quoted-printable';
                }
                $message[] = '';
            }
            $message[] = $this->formatMessage($this->textMessage, $needsEncoding);
            $message[] = '';
        }

        if (($emailFormat === 'html' || $emailFormat === 'both') && $this->htmlMessage) {
            if ($emailFormat === 'both') {
                $message[] = '--' . $altBoundary;
                $message[] = 'Content-Type: text/html; charset="' . $this->charset . '"';
                if ($needsEncoding) {
                    $message[] = 'Content-Transfer-Encoding: quoted-printable';
                }
                $message[] = '';
            }
            $message[] = $this->formatMessage($this->htmlMessage, $needsEncoding);
            $message[] = '';
        }

        if ($this->attachments) {
            foreach ($this->attachments as $filename => $name) {
                $mimeType = mime_content_type($filename);
                $message[] = '--' . $boundary;
                $message[] = "Content-Type: {$mimeType}; name=\"{$name}\"";
                $message[] = 'Content-Disposition: attachment';
                $message[] = 'Content-Transfer-Encoding: base64';
                $message[] = '';
                $message[] = chunk_split(base64_encode(file_get_contents($filename)));
                $message[] = '';
            }
        }
        if ($emailFormat === 'both' || $this->attachments) {
            $message[] = '--' . $boundary . '--';
        }

        return $message;
    }

    /**
     * Standardizes the line endings and encodes if needed
     *
     * @param string $message
     * @return string
     */
    protected function formatMessage(string $message, bool $needsEncoding): string
    {
        $message = preg_replace("/\r\n|\n/", "\r\n", $message);
        if ($needsEncoding) {
            $message = quoted_printable_encode($message);
        }

        return $message;
    }

    /**
     * Gets the message id for the message
     * if messageId is null [default] then it generates a UUID
     *
     * @return string
     */
    protected function getMessageId(): string
    {
        if ($this->messageId === null) {
            $this->messageId = $this->uuid();
        }

        /**
         * @link https://tools.ietf.org/html/rfc2822
         */
        return '<' . $this->messageId . '@' . $this->getDomain() . '>';
    }

    /**
     * Generates a UUID 4
     *
     * @return string
     */
    private function uuid(): string
    {
        return implode('-', [
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(6)),
        ]);
    }

    /**
     * Gets the domain to be used for message id generation
     * @return string
     */
    public function getDomain(): string
    {
        $domain = php_uname('n');
        if ($this->from) {
            $email = $this->from[0];
            list(, $domain) = explode('@', $email);
        }

        return $domain;
    }

    /**
     * Gets the boundary to be used in the email, if not set it will generate a unique id
     *
     * @return string
     */
    protected function getBoundary(): string
    {
        if ($this->boundary === null) {
            $this->boundary = md5(random_bytes(16));
        }

        return $this->boundary;
    }

    /**
     * Gets and encodes the subject
     *
     * @return string
     */
    protected function getSubject(): string
    {
        if ($this->subject) {
            $this->subject = mb_encode_mimeheader($this->subject, $this->charset, 'B');
        }

        return $this->subject;
    }

    /**
     * Gets the content type for the email
     *
     * @return string
     */
    protected function getContentType(): string
    {
        if ($this->attachments) {
            return 'multipart/mixed; boundary="' . $this->getBoundary() . '"';
        }
        $emailFormat = $this->format();

        if ($emailFormat === 'both') {
            return 'multipart/alternative; boundary="' . $this->getBoundary() . '"';
        }
        if ($emailFormat === 'html') {
            return 'text/html; charset="' . $this->charset . '"';
        }

        return 'text/plain; charset="' . $this->charset . '"';
    }

    /**
     * Gets/Sets the email format
     *
     * @param string|null $format html, text or both
     * @return string|\Origin\Email\Email
     */
    public function format($format = null)
    {
        if ($format === null) {
            return $this->emailFormat;
        }
        if (! in_array($format, ['text', 'html', 'both'])) {
            throw new InvalidArgumentException('Invalid email format');
        }
        $this->emailFormat = $format;

        return $this;
    }
    /**
     * Checks if a message needs to be encoded
     *
     * @return bool
     */
    protected function needsEncoding(): bool
    {
        $emailFormat = $this->format();

        if (($emailFormat === 'text' || $emailFormat === 'both') and mb_check_encoding($this->textMessage, 'ASCII') === false) {
            return true;
        }
        if (($emailFormat === 'html' || $emailFormat === 'both') and mb_check_encoding($this->htmlMessage, 'ASCII') === false) {
            return true;
        }

        return false;
    }

    /**
     * Returns a formatted address
     *
     * @param array $address
     * @return string james@originphp.com James <james@originphp.com>
     */
    protected function formatAddress(array $address): string
    {
        list($email, $name) = $address;
        if ($name === null) {
            return $email;
        }
        $name = mb_encode_mimeheader($name, $this->charset, 'B');

        return "{$name} <{$email}>";
    }

    /**
     * Formats multiple email addresses to be used within email headers
     *
     * @param array $addresses
     * @return string
     */
    protected function formatAddresses(array $addresses): string
    {
        $result = [];
        foreach ($addresses as $address) {
            $result[] = $this->formatAddress($address);
        }

        return implode(', ', $result);
    }
}
