<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CHL7;

/**
 * Class CSWF
 * Scheduled Workflow
 */
class CSWF extends CIHE
{
    /**
     * @var array
     */
    public static $versions = [
        "2.3",
        "2.3.1",
        "2.4",
        "2.5",
        "2.5.1",
    ];

    /**
     * @var array
     */
    public static $transaction_rad3 = [
        "O01",
    ];

    /**
     * @var array
     */
    public static $transaction_rad48 = [
        "S12",
        "S13",
        "S14",
        "S15",
        "S16",
        "S17",
        "S26",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // SIU
        "S12" => "CHL7EventSIUS12",
        "S13" => "CHL7EventSIUS13",
        "S14" => "CHL7EventSIUS14",
        "S15" => "CHL7EventSIUS15",
        "S16" => "CHL7EventSIUS16",
        "S17" => "CHL7EventSIUS17",
        "S26" => "CHL7EventSIUS26",

        // ORM
        "O01" => "CHL7EventORMO01",
    ];

    /**
     * Construct
     *
     * @return CSWF
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_RAD;
        $this->type   = "SWF";

        $this->_categories = [
            "RAD-3"  => self::$transaction_rad3,
            "RAD-48" => self::$transaction_rad48,
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
     * @return string Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        if (in_array($code, self::$transaction_rad3)) {
            return "RAD3";
        }

        if (in_array($code, self::$transaction_rad48)) {
            return "RAD48";
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
        $code    = $exchange->code;
        $version = $exchange->version;

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                // Transaction RAD-48
                if (in_array($code, self::$transaction_rad48)) {
                    $classname = "CHL7{$_version}EventSIU$code";
                }

                // Transaction RAD-3
                if (in_array($code, self::$transaction_rad3)) {
                    $classname = "CHL7{$_version}EventORM$code";
                }

                return new $classname();
            }
        }
    }
}
