<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
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

class Message
{
    /**
     * The message header
     *
     * @var string
     */
    private $header = null;

    /**
     * The message body
     *
     * @var string
     */
    private $body = null;

    /**
     * Constructor
     *
     * @param string $header
     * @param string $body
     */
    public function __construct(string $header, string $body)
    {
        $this->header = $header;
        $this->body = $body;
    }

    /**
     * Gets the message header
     *
     * @return string
     */
    public function header() : string
    {
        return $this->header;
    }

    /**
     * Gets the message body
     *
     * @return string
     */
    public function body() : string
    {
        return $this->body;
    }

    /**
     * Returns the full message (header and body)
     *
     * @return string
     */
    public function message() : string
    {
        return $this->header . "\r\n\r\n" . $this->body;
    }

    /**
     * Magic method for converting this object to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->message();
    }
}
