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
 * Class CLTW
 * Laboratory Testing Workflow
 */
class CLTW extends CIHE
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
    public static $transaction_lab3 = [
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
     * @return CLTW
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_PaLM;
        $this->type   = "LTW";

        $this->_categories = [
            "LAB-3" => self::$transaction_lab3,
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
        if (in_array($code, self::$transaction_lab3)) {
            return "LAB3";
        }
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
                // Transaction LAB-3
                if (in_array($code, self::$transaction_lab3)) {
                    $classname = "CHL7{$_version}EventORU$code";

                    return new $classname();
                }
            }
        }
    }
}
