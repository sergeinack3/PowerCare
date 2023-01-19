<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

/**
 * Description
 */
class CSyslogITI extends CInteropNorm
{
    /** @var array */
    public static $evenements = [
        "iti9"  => "CSyslogITI9",
        "iti21" => "CSyslogITI21",
        "iti22" => "CSyslogITI22",
        "iti41" => "CSyslogITI41",
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->type   = "iti";
        $this->domain = self::DOMAIN_SYSLOG;

        parent::__construct();
    }

    /**
     * @see parent::getEvenements()
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }
}
