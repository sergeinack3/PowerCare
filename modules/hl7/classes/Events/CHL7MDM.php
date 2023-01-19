<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CHL7;

/**
 * Class CHL7MDM
 * Medical Records/Information Management
 */
class CHL7MDM extends CHL7
{
    /**
     * @var array
     */
    public static $versions = [
        '2.6',
    ];

    /**
     * @var array
     */
    public static $evenements = [
        'T02' => 'CHL7EventMDMT02',
        'T04' => 'CHL7EventMDMT04',
        'T10' => 'CHL7EventMDMT10',
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->domain = 'HL7';
        $this->type   = 'MDM';

        parent::__construct();
    }

    /**
     * @see parent::getEvent
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $code    = $exchange->code;
        $version = $exchange->version;

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                $classname = "CHL7{$_version}EventMDM$code";

                return new $classname();
            }
        }

        return null;
    }

    /**
     * @see parent::getEvenements
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @see parent::getVersions
     */
    public function getVersions(): ?array
    {
        return self::$versions;
    }
}
