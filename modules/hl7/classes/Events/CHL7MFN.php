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
 * Class CHL7MFN
 * Master File Notification
 */
class CHL7MFN extends CHL7
{
    /**
     * @var array
     */
    public static $versions = [
        '2.1',
        '2.2',
        '2.3',
        '2.4',
        '2.5',
        '2.6',
    ];

    /**
     * @var array
     */
    public static $evenements = [
        'M05' => 'CHL7EventMFNM05',
        'M15' => 'CHL7EventMFNM15',
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_HL7;
        $this->type   = 'MFN';

        parent::__construct();
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

    /**
     * @see parent::getEvent
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $code    = $exchange->code;
        $version = $exchange->version;

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                $classname = "CHL7{$_version}EventMFN$code";

                return new $classname();
            }
        }

        return null;
    }
}
