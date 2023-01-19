<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7;

/**
 * Class CSINR
 * Simple Image and Numeric Report
 */
class CSINR extends CIHE
{
    /**
     * @var array
     */
    public static $versions = [
        "2.1",
        "2.2",
        "2.3",
        "2.4",
        "2.5",
        "2.5.1",
        "2.6",
    ];

    /**
     * @var array
     */
    public static $transaction_rad28 = [
        "R01",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // R01
        "R01" => "CHL7EventORUR01",
    ];

    /**
     * Construct
     *
     * @return CSINR
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_RAD;
        $this->type   = "SINR";

        $this->_categories = [
            "RAD-28" => self::$transaction_rad28,
        ];

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
     * Retrieve transaction name
     *
     * @param string $code Event code
     *
     * @return string|null Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        if (in_array($code, self::$transaction_rad28)) {
            return "RAD28";
        }

        return null;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        /** @var CExchangeHL7v2 $exchange */
        $code    = $exchange->code;
        $version = $exchange->version;

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                // Transaction RAD-28
                if (in_array($code, self::$transaction_rad28)) {
                    $classname = "CHL7{$_version}EventORU$code";

                    return new $classname();
                }
            }
        }
    }
}
