<?php
namespace PHPMailer\PHPMailer;

/**
 * PHPMailer - PHP SMTP email transport class
 * NOTE: Designed for use with PHP version 5.0 and above
 *
 * @package PHPMailer
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Marcus Bointon (coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author PHPMailer contributors (see CONTRIBUTORS file)
 * @copyright 2004 - 2014 Andy Prevost
 * @copyright 2012 - 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer - PHP SMTP email transport class
 *
 * @package PHPMailer
 */
class SMTP
{
    /**
     * The PHPMailer SMTP version number.
     *
     * @var string
     */
    const VERSION = '6.1.7';

    /**
     * SMTP line break constant.
     *
     * @var string
     */
    const CRLF = "\r\n";

    /**
     * The SMTP port to use if one is not specified.
     *
     * @var int
     */
    const DEFAULT_PORT = 25;

    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 998;

    /**
     * Debug level for no output
     *
     * @var int
     */
    const DEBUG_OFF = 0;

    /**
     * Debug level to show client -> server messages
     *
     * @var int
     */
    const DEBUG_CLIENT = 1;

    /**
     * Debug level to show client -> server and server -> client messages
     *
     * @var int
     */
    const DEBUG_SERVER = 2;

    /**
     * Debug level to show connection status, client -> server and server -> client messages
     *
     * @var int
     */
    const DEBUG_CONNECTION = 3;

    /**
     * Debug level to show all messages
     *
     * @var int
     */
    const DEBUG_LOWLEVEL = 4;

    /**
     * Debug output level.
     * Options:
     * * self::DEBUG_OFF (`0`) No debug output, default
     * * self::DEBUG_CLIENT (`1`) Client commands
     * * self::DEBUG_SERVER (`2`) Client commands and server responses
     * * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     * * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages
     *
     * @var int
     */
    public $do_debug = self::DEBUG_OFF;

    /**
     * How to handle debug output.
     * Options:
     * * `echo` Output plain text as is, appropriate for CLI
     * * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * * `error_log` Output to error log as configured in php.ini
     *
     * @var string
     */
    public $Debugoutput = 'echo';

    /**
     * Whether to use VERP.
     *
     * @var bool
     */
    public $do_verp = false;

    /**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is set in constructor.
     *
     * @var int
     */
    public $Timeout = 300;

    /**
     * How long to wait for commands to complete, in seconds.
     * Default of 5 minutes (300sec) is set in constructor.
     *
     * @var int
     */
    public $Timelimit = 300;

    /**
     * The socket for the server connection.
     *
     * @var resource
     */
    protected $smtp_conn;

    /**
     * Error information, if any, for the last SMTP command.
     *
     * @var array
     */
    protected $error = [
        'error' => '',
        'detail' => '',
        'smtp_code' => '',
        'smtp_code_ex' => ''
    ];

    /**
     * The reply the server sent to us for HELO.
     * If null, no HELO string has yet been received.
     *
     * @var string|null
     */
    protected $helo_rply = null;

    /**
     * The set of SMTP extensions sent in reply to EHLO command.
     * Indexes of the array are extension names.
     * Values are true if the extension is supported, false if not.
     *
     * @var array
     */
    protected $server_hashes = [];

    /**
     * When sending mail RCPT TO: <user@domain.com> we also look for
     * RCPT TO: <user@[domain.com]> as one SMTP servers in Argentina appear to use this
     * non standard format, but we could not find any document to say this is standard
     * or not, we found this first mentioned in @link https://www.drupal.org/node/280662
     *
     * @var bool
     */
    protected $rset = true;

    /**
     * The most recent reply received from the server.
     *
     * @var string
     */
    protected $last_reply = '';

    /**
     * Output debugging information via a user-selected method.
     *
     * @param string $str Debug string to output
     * @param int    $level The current debug level:
     *                      * self::DEBUG_OFF (`0`) No debug output, default
     *                      * self::DEBUG_CLIENT (`1`) Client commands
     *                      * self::DEBUG_SERVER (`2`) Client commands and server responses
     *                      * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     *                      * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages
     *
     * @see Debugoutput
     * @see do_debug
     */
    protected function edebug($str, $level = 0)
    {
        if ($this->do_debug < $level) {
            return;
        }

        //Is this a PSR-3 logger?
        if ($this->Debugoutput instanceof \Psr\Log\LoggerInterface) {
            $this->Debugoutput->debug($str);
            return;
        }

        //Avoid clash with built-in function names
        //Is this the built-in function call?
        if (is_callable($this->Debugoutput) && !in_array($this->Debugoutput, ['error_log', 'html', 'echo'])) {
            call_user_func($this->Debugoutput, $str, $level);
            return;
        }

        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo htmlentities(
                    preg_replace('/[\r\n]+/', '', $str),
                    ENT_QUOTES,
                    'UTF-8'
                )
                . "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/ms', "\n", $str);
                echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
                    "\n",
                    "\n                   \t                  ",
                    trim($str)
                ) . "\n";
        }
    }

    /**
     * Connect to an SMTP server.
     *
     * @param string $host    SMTP server IP or host name
     * @param int    $port    The port number to connect to
     * @param int    $timeout How long to wait for the connection to open
     * @param array  $options An array of options for stream_context_create()
     *
     * @return bool
     */
    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        // Clear errors to avoid confusion
        $this->error = [
            'error' => '',
            'detail' => '',
            'smtp_code' => '',
            'smtp_code_ex' => ''
        ];

        // Make sure we are __not__ connected
        if ($this->connected()) {
            // Already connected, generate an error
            $this->error = ['error' => 'Already connected to a server'];
            return false;
        }

        if (empty($port)) {
            $port = self::DEFAULT_PORT;
        }

        // Connect to the SMTP server
        $this->edebug(
            "Connection: opening to $host:$port, timeout=$timeout, options=" .
            (count($options) > 0 ? var_export($options, true) : 'array()'),
            self::DEBUG_CONNECTION
        );

        $errno = 0;
        $errstr = '';
        $socket_context = stream_context_create($options);
        //Suppress errors here; we'll handle them ourselves
        $this->smtp_conn = @stream_socket_client(
            $host . ":" . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $socket_context
        );

        // Verify we connected properly
        if (empty($this->smtp_conn)) {
            $this->error = [
                'error' => 'Failed to connect to server',
                'errno' => $errno,
                'errstr' => $errstr
            ];
            $this->edebug(
                'SMTP ERROR: ' . $this->error['error']
                . ": $errstr ($errno)",
                self::DEBUG_CLIENT
            );
            return false;
        }

        if (substr(PHP_OS, 0, 3) != 'WIN') {
            $max = ini_get('max_execution_time');
            if ($max != 0 && $timeout > $max) {
                @set_time_limit($timeout);
            }
            stream_set_timeout($this->smtp_conn, $timeout, 0);
        }

        // Annoyingly, fgets doesn't work with a timeout in PHP < 5.2.2
        // This means we need to know the timeout in the stream timeout, but that is not always available
        $announce = $this->get_lines();

        $this->edebug('SERVER -> CLIENT: ' . $announce, self::DEBUG_SERVER);

        return true;
    }

    /**
     * Initiate a TLS (encrypted) session.
     *
     * @return bool
     */
    public function startTLS()
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }

        // Begin encrypted connection
        if (!stream_socket_enable_crypto(
            $this->smtp_conn,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        )) {
            $this->error = ['error' => 'STARTTLS failed'];
            return false;
        }

        return true;
    }

    /**
     * Perform SMTP authentication.
     * Must be run after hello().
     *
     * @param string $username The user name
     * @param string $password The password
     * @param string $authtype The auth type (CRAM-MD5, PLAIN, LOGIN, XOAUTH2)
     * @param string $realm    The auth realm for NTLM
     *
     * @return bool True if successfully authenticated
     */
    public function authenticate(
        $username,
        $password,
        $authtype = 'LOGIN',
        $realm = ''
    ) {
        if (empty($authtype)) {
            $authtype = 'LOGIN';
        }

        switch ($authtype) {
            case 'PLAIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH PLAIN', 334)) {
                    return false;
                }
                // Send encoded username and password
                if (!$this->sendCommand(
                    'User & Password',
                    base64_encode("\0" . $username . "\0" . $password),
                    235
                )) {
                    return false;
                }
                break;
            case 'LOGIN':
                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH LOGIN', 334)) {
                    return false;
                }
                // Send encoded username
                if (!$this->sendCommand('Username', base64_encode($username), 334)) {
                    return false;
                }
                // Send encoded password
                if (!$this->sendCommand('Password', base64_encode($password), 235)) {
                    return false;
                }
                break;
            case 'XOAUTH2':
                //If the OAuth Instance is not set. Can be a case while testing without using PHPMailer
                if (is_null($this->oauth)) {
                    return false;
                }
                $oauth = $this->oauth->getOauth64();

                // Start authentication
                if (!$this->sendCommand('AUTH', 'AUTH XOAUTH2 ' . $oauth, 235)) {
                    return false;
                }
                break;
            default:
                $this->error = ['error' => 'Authentication method not supported'];
                return false;
        }

        return true;
    }

    /**
     * Send an SMTP DATA command.
     * Issues a data command and sends the msg_data to the server,
     * finializing the mail transaction. $msg_data is the message
     * that is to be send with the headers. $header_data is the
     * SMTP Envelope item name such as HELO, MAIL FROM, RCPT TO, etc.
     *
     * @param string $msg_data Message data to send
     *
     * @return bool
     */
    public function data($msg_data)
    {
        //This will use the standard timelimit which is lower
        //max_execution_time default 30 seconds
        $max_execution_time = ini_get('max_execution_time');

        // Specify 180 seconds timeout at here, in case of sending
        // large email with many attachments.
        $smtp_timeout = 180;
        if ($max_execution_time != 0 && $smtp_timeout > $max_execution_time) {
            $this->edebug(
                'Sending: This can take longer than the max_execution_time setting on your system. ' .
                'See https://www.php.net/manual/en/function.set-time-limit.php for more information.',
                self::DEBUG_LOWLEVEL
            );
        }

        // Send the message to the server
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }

        /* The server is ready to accept data!
         * According to rfc 821 we should not send more than 1000
         * characters in a single line (including the CRLF)
         * so we will break the data up into lines by \r and/or \n
         * if they are not already too long
         */

        // Normalize line breaks before exploding
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $msg_data));

        /* To avoid creating a new string in the loop, we'll
         * set a flag to tell us if our string is currently in
         * an escape sequence.
         */
        $in_escape = false;

        foreach ($lines as $line) {
            $lines_out = [];

            if ($in_escape and !empty($line)) {
                $lines_out[] = $line;
            } else {
                $trim = trim($line);
                if (str_starts_with($trim, '.')) {
                    $lines_out[] = '.' . $trim;
                } else {
                    $lines_out[] = $trim;
                }
            }

            /* Now send the lines to the server */
            foreach ($lines_out as $line_out) {
                //Don't add CRLF on the last line
                if ($line_out !== end($lines_out)) {
                    $line_out .= self::CRLF;
                }
                if (!$this->sendCommand('DATA', $line_out, 220)) {
                    return false;
                }
            }
        }

        // Message data has been sent, complete the command
        if (!$this->sendCommand('DATA END', '.', 250)) {
            return false;
        }

        return true;
    }

    /**
     * Send an SMTP HELO or EHLO command.
     * Used to identify the sending server to the receiving server.
     * This makes sure that client and server are in a known state.
     * Implements RFC 821: HELO <SP> <domain> <CRLF>
     * and RFC 2821 EHLO.
     *
     * @param string $host The host name or IP to connect to
     *
     * @return bool
     */
    public function hello($host = '')
    {
        // Support for older, less-compliant remote SMTP servers
        // If empty send 'HELO' instead of 'EHLO'
        if (empty($this->server_hashes)) {
            if (!$this->sendCommand('HELO', 'HELO ' . $host, 250)) {
                return false;
            }
        } else {
            if (!$this->sendCommand('EHLO', 'EHLO ' . $host, 250)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send an SMTP MAIL FROM command.
     * Starts a mail transaction from the email address specified in
     * $from. Returns true if successful or false otherwise. If True
     * the mail transaction is started and then one or more RCPT
     * commands may be called followed by a data command.
     *
     * @param string $from Source address of this email
     *
     * @return bool
     */
    public function mail($from)
    {
        $useVerp = ($this->do_verp ? ' XVERP' : '');
        if (!$this->sendCommand('MAIL FROM', 'MAIL FROM:<' . $from . '>' . $useVerp, 250)) {
            return false;
        }

        return true;
    }

    /**
     * Send an SMTP QUIT command.
     * Closes the socket if there is no error or the $close_on_error
     * argument is true.
     * Implements from RFC 821: QUIT <CRLF>.
     *
     * @param bool $close_on_error Should the connection close if an error occurs?
     *
     * @return bool
     */
    public function quit($close_on_error = false)
    {
        $noerror = $this->sendCommand('QUIT', 'QUIT', 221);
        $err = $this->error; // Save any error

        if ($noerror || $close_on_error) {
            $this->close();
            $this->error = $err; // Restore any error from the quit command
        }

        return $noerror;
    }

    /**
     * Send an SMTP RCPT TO command.
     * Sets a recipient for the email. Returns true if the recipient was
     * accepted false if it was rejected. Implements from RFC 821:
     * RCPT <SP> TO:<forward-path> <CRLF>.
     *
     * @param string $to The address the email is being sent to
     *
     * @return bool
     */
    public function recipient($to)
    {
        if (!$this->sendCommand('RCPT TO', 'RCPT TO:<' . $to . '>', [250, 251])) {
            return false;
        }

        return true;
    }

    /**
     * Send an SMTP RSET command.
     * Abort any transaction that is currently in progress.
     * Implements RFC 821: RSET <CRLF>.
     *
     * @return bool True on success
     */
    public function reset()
    {
        if (!$this->sendCommand('RSET', 'RSET', 250)) {
            return false;
        }

        return true;
    }

    /**
     * Send a command to an SMTP server and check its return code.
     *
     * @param string    $command       The command name - not sent to the server
     * @param string    $commandstring The actual command to send to the server
     * @param int|array $expect        One or more expected integer success codes
     *
     * @return bool True on success
     */
    protected function sendCommand($command, $commandstring, $expect)
    {
        if (!$this->connected()) {
            $this->error = [
                'error' => "Called $command without being connected"
            ];
            return false;
        }

        $this->client_send($commandstring . self::CRLF);

        $this->last_reply = $this->get_lines();

        $this->edebug('SERVER -> CLIENT: ' . $this->last_reply, self::DEBUG_SERVER);

        $code = substr($this->last_reply, 0, 3);

        if (!in_array($code, (array) $expect)) {
            $this->error = [
                'error' => "$command command failed",
                'smtp_code' => $code,
                'detail' => substr($this->last_reply, 4)
            ];
            $this->edebug(
                'SMTP ERROR: ' . $this->error['error'] . ': ' . $this->last_reply,
                self::DEBUG_CLIENT
            );
            return false;
        }

        return true;
    }

    /**
     * Send raw data to the server.
     *
     * @param string $data The data to send
     *
     * @return int|bool The number of bytes sent to the server or false on error
     */
    public function client_send($data)
    {
        $this->edebug("CLIENT -> SERVER: $data", self::DEBUG_CLIENT);
        set_error_handler([$this, 'customErrorHandler']);
        $result = fwrite($this->smtp_conn, $data);
        restore_error_handler();

        return $result;
    }

    /**
     * Get the latest error.
     *
     * @return array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get the last reply from the server.
     *
     * @return string
     */
    public function getLastReply()
    {
        return $this->last_reply;
    }

    /**
     * Get the connection status.
     *
     * @return bool True if connected
     */
    public function connected()
    {
        if (!empty($this->smtp_conn)) {
            $sock_status = stream_get_meta_data($this->smtp_conn);
            if ($sock_status['eof']) {
                // The socket is valid but we are not connected
                $this->edebug(
                    'SMTP NOTICE: EOF caught while checking if connected',
                    self::DEBUG_CLIENT
                );
                $this->close();
                return false;
            }
            return true; // everything looks good
        }

        return false;
    }

    /**
     * Close the socket and clean up the state of the class.
     * Don't use this function without first trying to use QUIT.
     *
     * @see quit()
     */
    public function close()
    {
        $this->error = [];
        $this->server_hashes = [];
        $this->last_reply = '';

        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = 0;
        }
    }

    /**
     * Get the last line from the server.
     *
     * @return string A line of response from the server
     */
    protected function get_lines()
    {
        // If the connection is bad, give up now
        if (!is_resource($this->smtp_conn)) {
            return '';
        }

        $data = '';
        $endtime = 0;
        stream_set_timeout($this->smtp_conn, $this->Timeout);
        if ($this->Timelimit > 0) {
            $endtime = time() + $this->Timelimit;
        }
        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = fgets($this->smtp_conn, 515);
            if ($str === false) {
                if (empty($data)) {
                    $this->edebug(
                        'SMTP NOTICE: fgets() returned false',
                        self::DEBUG_LOWLEVEL
                    );
                    return '';
                }
                $this->edebug(
                    'SMTP NOTICE: fgets() returned false',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }

            $data .= $str;

            // If response is only 3 chars (not 4), the connection is closed
            if (strlen($str) < 4) {
                $this->edebug(
                    'SMTP NOTICE: Short response, bailing out',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
            // Mark the last chars of the response
            if (substr($str, 3, 1) == ' ') {
                break;
            }
            // If a blank line, we're done
            if ($str == "\r\n") {
                break;
            }
            // Timed-out?
            $info = stream_get_meta_data($this->smtp_conn);
            if ($info['timed_out']) {
                $this->edebug(
                    'SMTP NOTICE: Timeout while fetching data',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
            // Now check if reads took too long
            if ($endtime && time() > $endtime) {
                $this->edebug(
                    'SMTP NOTICE: Timelimit reached while fetching data',
                    self::DEBUG_LOWLEVEL
                );
                break;
            }
        }

        return $data;
    }

    /**
     * Enable or disable VERP address generation.
     *
     * @param bool $enabled
     */
    public function setVerp($enabled = false)
    {
        $this->do_verp = $enabled;
    }

    /**
     * Get VERP address generation mode.
     *
     * @return bool
     */
    public function getVerp()
    {
        return $this->do_verp;
    }

    /**
     * Set debug output method.
     *
     * @param string|callable $method The name of the mechanism to use for debugging output.
     *                                See the `Debugoutput` property for more details.
     */
    public function setDebugOutput($method = 'echo')
    {
        $this->Debugoutput = $method;
    }

    /**
     * Get debug output method.
     *
     * @return string
     */
    public function getDebugOutput()
    {
        return $this->Debugoutput;
    }

    /**
     * Set debug output level.
     *
     * @param int $level
     */
    public function setDebugLevel($level = 0)
    {
        $this->do_debug = $level;
    }

    /**
     * Get debug output level.
     *
     * @return int
     */
    public function getDebugLevel()
    {
        return $this->do_debug;
    }

    /**
     * Set SMTP timeout.
     *
     * @param int $timeout
     */
    public function setTimeout($timeout = 0)
    {
        $this->Timeout = $timeout;
    }

    /**
     * Get SMTP timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->Timeout;
    }

    /**
     * Reports an error number and string.
     *
     * @param int    $errno   Error number
     * @param string $errmsg  Error message
     * @param string $errfile Error filepath
     * @param int    $errline Error line number
     *
     * @return bool
     */
    protected function customErrorHandler($errno, $errmsg, $errfile = '', $errline = 0)
    {
        $this->error = [
            'error' => $errmsg,
            'errno' => $errno,
            'errfile' => $errfile,
            'errline' => $errline
        ];
        $this->edebug(
            'SMTP ERROR: ' . $this->error['error'],
            self::DEBUG_CLIENT
        );

        return true;
    }
} 