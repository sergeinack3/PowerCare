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
 * Class CPAMFRA
 * Patient Administration Management - National extension France
 */
class CPAMFRA extends CPAM
{
    /**
     * @var array
     */
    public static $versions = [
        "2.3",
        "2.4",
        "2.5",
        "2.6",
        "2.7",
        "2.8",
        "2.9",
        "2.10",
    ];

    /**
     * @var array
     */
    public static $transaction_iti30 = [
        "A24",
        "A37",
        "A28",
        "A31",
        "A40",
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
        "A11",
        "A12",
        "A13",
        "A14",
        "A16",
        "A21",
        "A22",
        "A25",
        "A38",
        "A44",
        "A49",
        "A52",
        "A53",
        "A54",
        "A55",
        "Z80",
        "Z81",
        "Z84",
        "Z85",
        "Z99",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-30
        "A24" => "CHL7EventADTA24_FRA",
        "A28" => "CHL7EventADTA28_FRA",
        "A31" => "CHL7EventADTA31_FRA",
        "A37" => "CHL7EventADTA37_FRA",
        "A40" => "CHL7EventADTA40_FRA",
        "A47" => "CHL7EventADTA47_FRA",

        // ITI-31
        "A01" => "CHL7EventADTA01_FRA",
        "A02" => "CHL7EventADTA02_FRA",
        "A03" => "CHL7EventADTA03_FRA",
        "A04" => "CHL7EventADTA04_FRA",
        "A05" => "CHL7EventADTA05_FRA",
        "A06" => "CHL7EventADTA06_FRA",
        "A07" => "CHL7EventADTA07_FRA",
        "A11" => "CHL7EventADTA11_FRA",
        "A12" => "CHL7EventADTA12_FRA",
        "A13" => "CHL7EventADTA13_FRA",
        "A14" => "CHL7EventADTA14_FRA",
        "A16" => "CHL7EventADTA16_FRA",
        "A21" => "CHL7EventADTA21_FRA",
        "A22" => "CHL7EventADTA22_FRA",
        "A25" => "CHL7EventADTA25_FRA",
        "A38" => "CHL7EventADTA38_FRA",
        "A44" => "CHL7EventADTA44_FRA",
        "A49" => "CHL7EventADTA49_FRA",
        "A52" => "CHL7EventADTA52_FRA",
        "A53" => "CHL7EventADTA53_FRA",
        "A54" => "CHL7EventADTA54_FRA",
        "A55" => "CHL7EventADTA55_FRA",
        "Z80" => "CHL7EventADTZ80_FRA",
        "Z81" => "CHL7EventADTZ81_FRA",
        "Z84" => "CHL7EventADTZ84_FRA",
        "Z85" => "CHL7EventADTZ85_FRA",
        "Z99" => "CHL7EventADTZ99_FRA",
    ];

    /**
     * Construct
     *
     * @return CPAMFRA
     */
    public function __construct()
    {
        parent::__construct();

        $this->domain = self::DOMAIN_ITI;
        $this->type   = "PAM_FRA";

        $this->_categories = [
            "ITI-30" => self::$transaction_iti30,
            "ITI-31" => self::$transaction_iti31,
        ];
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
        if (in_array($code, self::$transaction_iti30)) {
            return "ITI30";
        }

        if (in_array($code, self::$transaction_iti31)) {
            return "ITI31";
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
                $classname = "CHL7{$_version}EventADT{$code}_FRA";

                return new $classname;
            }
        }
    }
}
