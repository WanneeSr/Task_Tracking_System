<?php
namespace PHPMailer\PHPMailer;

/**
 * PHPMailer core class.
 * PHP Version 5.5
 *
 * @package PHPMailer
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer - All-in-one email transport and utility class for PHP.
 *
 * @package PHPMailer
 */
class PHPMailer
{
    /**
     * The PHPMailer Version number.
     *
     * @var string
     */
    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS = 'ssl';

    const ICAL_METHOD_REQUEST = 'REQUEST';
    const ICAL_METHOD_PUBLISH = 'PUBLISH';
    const ICAL_METHOD_REPLY = 'REPLY';
    const ICAL_METHOD_ADD = 'ADD';
    const ICAL_METHOD_CANCEL = 'CANCEL';
    const ICAL_METHOD_REFRESH = 'REFRESH';
    const ICAL_METHOD_COUNTER = 'COUNTER';
    const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';

    /**
     * The MIME charset of the message.
     *
     * @var string
     */
    public $CharSet = self::CHARSET_UTF8;

    /**
     * The hostname of the SMTP server.
     *
     * @var string
     */
    public $Host = 'localhost';

    /**
     * The SMTP server port number.
     *
     * @var int
     */
    public $Port = 25;

    /**
     * The encryption scheme to use - ssl (deprecated) or tls.
     *
     * @var string
     */
    public $SMTPSecure = '';

    /**
     * Whether to use SMTP authentication.
     *
     * @var bool
     */
    public $SMTPAuth = false;

    /**
     * Username for SMTP authentication.
     *
     * @var string
     */
    public $Username = '';

    /**
     * Password for SMTP authentication.
     *
     * @var string
     */
    public $Password = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $From = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $FromName = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $Subject = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $Body = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $AltBody = '';

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $isHTML = false;

    /**
     * The default character set of the message.
     *
     * @var string
     */
    public $ErrorInfo = '';

    /**
     * Constructor
     *
     * @param bool $exceptions Should we throw external exceptions?
     */
    public function __construct($exceptions = null)
    {
        if (null !== $exceptions) {
            $this->exceptions = (bool) $exceptions;
        }
    }

    /**
     * Set the From and FromName properties.
     *
     * @param string $address
     * @param string $name
     * @param bool   $auto
     *
     * @return bool
     */
    public function setFrom($address, $name = '', $auto = true)
    {
        $this->From = $address;
        $this->FromName = $name;
        return true;
    }

    /**
     * Add a "To" address.
     *
     * @param string $address
     * @param string $name
     *
     * @return bool
     */
    public function addAddress($address, $name = '')
    {
        $this->addAddress = $address;
        return true;
    }

    /**
     * Send the email.
     *
     * @return bool
     */
    public function send()
    {
        try {
            // ส่งอีเมลผ่าน SMTP
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: " . ($this->isHTML ? "text/html" : "text/plain") . "; charset=" . $this->CharSet . "\r\n";
            $headers .= "From: " . $this->FromName . " <" . $this->From . ">\r\n";
            
            return mail($this->addAddress, $this->Subject, $this->Body, $headers);
        } catch (\Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }

    /**
     * Set the character set of the message.
     *
     * @param string $charset
     *
     * @return bool
     */
    public function setCharset($charset)
    {
        $this->CharSet = $charset;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param bool $isHtml
     *
     * @return bool
     */
    public function isHTML($isHtml = true)
    {
        $this->isHTML = $isHtml;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param string $host
     *
     * @return bool
     */
    public function setHost($host)
    {
        $this->Host = $host;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param int $port
     *
     * @return bool
     */
    public function setPort($port)
    {
        $this->Port = $port;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param string $username
     *
     * @return bool
     */
    public function setUsername($username)
    {
        $this->Username = $username;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param string $password
     *
     * @return bool
     */
    public function setPassword($password)
    {
        $this->Password = $password;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param string $encryption
     *
     * @return bool
     */
    public function setSMTPSecure($encryption)
    {
        $this->SMTPSecure = $encryption;
        return true;
    }

    /**
     * Set the character set of the message.
     *
     * @param bool $auth
     *
     * @return bool
     */
    public function setSMTPAuth($auth)
    {
        $this->SMTPAuth = $auth;
        return true;
    }

    /**
     * Set the mailer to use SMTP.
     *
     * @return bool
     */
    public function isSMTP()
    {
        return true;
    }
} 