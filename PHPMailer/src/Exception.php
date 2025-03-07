<?php
namespace PHPMailer\PHPMailer;

/**
 * PHPMailer exception handler
 *
 * @package PHPMailer
 * @author Sebastian Tschan
 * @author Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
class Exception extends \Exception
{
    /**
     * Prettify error message output
     *
     * @return string
     */
    public function errorMessage()
    {
        return '<strong>' . htmlspecialchars($this->getMessage(), ENT_COMPAT | ENT_HTML401) . "</strong><br />\n";
    }
} 