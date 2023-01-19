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
 * Class CDRPT
 * Displayable Report
 */
class CDRPT extends CIHE
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
        "2.7",
        "2.8",
    ];

    /**
     * @var array
     */
    public static $transaction_card_7 = [
        'T02',
        'T04',
        'T10',
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // T02
        "T02" => "CHL7EventMDMT02",
        // T04
        "T04" => "CHL7EventMDMT04",
        // T10
        "T10" => "CHL7EventMDMT10",
    ];

    /**
     * Construct
     *
     * @return CDRPT
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_CARD;
        $this->type   = "DRPT";

        $this->_categories = [
            "CARD-7" => self::$transaction_card_7,
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
        if (in_array($code, self::$transaction_card_7)) {
            return "CARD7";
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
                // Transaction CARD-7
                if (in_array($code, self::$transaction_card_7)) {
                    $classname = "CHL7{$_version}EventMDM$code";

                    return new $classname();
                }
            }
        }
    }
}
