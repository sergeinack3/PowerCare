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
 * Class CPAM
 * Patient Administration Management
 */
class CPAM extends CIHE
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
    ];

    /**
     * @var array
     */
    public static $transaction_iti30 = [
        "A08",
        "A24",
        "A37",
        "A28",
        "A29",
        "A31",
        "A40",
        "A46",
        "A47",
    ];

    /**
     * @var array
     */
    public static $transaction_iti31 = [
        "A01",
        "A02",
        "A03",
        "A04",
        "A05",
        "A06",
        "A07",
        "A08",
        "A09",
        "A10",
        "A11",
        "A12",
        "A13",
        "A14",
        "A15",
        "A16",
        "A21",
        "A22",
        "A25",
        "A26",
        "A27",
        "A32",
        "A33",
        "A38",
        "A42",
        "A44",
        "A45",
        "A49",
        "A50",
        "A52",
        "A53",
        "A54",
        "A55",
        "Z99",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-30
        "A24" => "CHL7EventADTA24",
        "A28" => "CHL7EventADTA28",
        "A29" => "CHL7EventADTA29",
        "A31" => "CHL7EventADTA31",
        "A37" => "CHL7EventADTA37",
        "A40" => "CHL7EventADTA40",
        "A46" => "CHL7EventADTA46",
        "A47" => "CHL7EventADTA47",

        // ITI-31
        "A01" => "CHL7EventADTA01",
        "A02" => "CHL7EventADTA02",
        "A03" => "CHL7EventADTA03",
        "A04" => "CHL7EventADTA04",
        "A05" => "CHL7EventADTA05",
        "A06" => "CHL7EventADTA06",
        "A07" => "CHL7EventADTA07",
        "A08" => "CHL7EventADTA08",
        "A09" => "CHL7EventADTA09",
        "A10" => "CHL7EventADTA10",
        "A11" => "CHL7EventADTA11",
        "A12" => "CHL7EventADTA12",
        "A13" => "CHL7EventADTA13",
        "A14" => "CHL7EventADTA14",
        "A15" => "CHL7EventADTA15",
        "A16" => "CHL7EventADTA16",
        "A21" => "CHL7EventADTA21",
        "A22" => "CHL7EventADTA22",
        "A25" => "CHL7EventADTA25",
        "A26" => "CHL7EventADTA26",
        "A27" => "CHL7EventADTA27",
        "A32" => "CHL7EventADTA32",
        "A33" => "CHL7EventADTA33",
        "A38" => "CHL7EventADTA38",
        "A42" => "CHL7EventADTA42",
        "A44" => "CHL7EventADTA44",
        "A45" => "CHL7EventADTA45",
        "A49" => "CHL7EventADTA49",
        "A50" => "CHL7EventADTA50",
        "A52" => "CHL7EventADTA52",
        "A53" => "CHL7EventADTA53",
        "A54" => "CHL7EventADTA54",
        "A55" => "CHL7EventADTA55",
        "Z99" => "CHL7EventADTZ99",
    ];

    /**
     * Construct
     *
     * @return CPAM
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "PAM";

        $this->_categories = [
            "ITI-30" => self::$transaction_iti30,
            "ITI-31" => self::$transaction_iti31,
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
        if (in_array($code, self::$transaction_iti30)) {
            return "ITI30";
        }

        if (in_array($code, self::$transaction_iti31)) {
            return "ITI31";
        }

        return null;
    }

    /**
     * Retrieve transaction from actor
     *
     * @param string $actor_name Actor name
     *
     * @return array Messages
     */
    public static function getTransactionFromActor(string $actor_name): ?array
    {
        $actors = [
            "PDC" => self::$transaction_iti30,
            "PDS" => self::$transaction_iti30,

            "PEC" => self::$transaction_iti31,
            "PES" => self::$transaction_iti31,
        ];

        if (array_key_exists($actor_name, $actors)) {
            return $actors[$actor_name];
        }

        return [];
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object|null An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $code    = $exchange->code;
        $version = $exchange->version;

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                $classname = "CHL7{$_version}EventADT$code";

                return new $classname();
            }
        }

        return null;
    }
}
