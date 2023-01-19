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
 * Class CPDQ
 * Patient Demographics Query
 */
class CPDQ extends CIHE
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
    public static $transaction_iti21 = [
        "Q22",
        "J01",
    ];

    /**
     * @var array
     */
    public static $transaction_iti22 = [
        "ZV1",
    ];

    /**
     * @var array
     */
    public static $transaction_iti47 = [
        "PRPA_IN201305UV02",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // ITI-21
        "Q22" => "CHL7EventQBPQ22",
        "J01" => "CHL7EventQCNJ01",

        "PRPA_IN201305UV02" => "CHL7v3EventPRPAIN201305UV02",

        // ITI-22
        "ZV1"               => "CHL7EventQBPZV1",
    ];

    /**
     * Construct
     *
     * @return CPDQ
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "PDQ";

        $this->_categories = [
            "ITI-21" => self::$transaction_iti21,
            "ITI-22" => self::$transaction_iti22,
            "ITI-47" => self::$transaction_iti47,
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
     * Retrieve transaction name,
     *
     * @param string $code Event code
     *
     * @return string Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        if (in_array($code, self::$transaction_iti21)) {
            return "ITI21";
        }

        if (in_array($code, self::$transaction_iti22)) {
            return "ITI22";
        }

        if (in_array($code, self::$transaction_iti47)) {
            return "ITI47";
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
                $classname = null;

                if ($code == "Q22" || $code == "ZV1") {
                    $classname = "CHL7{$_version}EventQBP$code";
                }
                if ($code == "J01") {
                    $classname = "CHL7{$_version}EventQCN$code";
                }
                if ($code == "PRPA_IN201305UV02") {
                    $classname = "CHL7v3Event$code";
                }

                return $classname ? new $classname() : null;
            }
        }
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
            "PDC" => array_merge(self::$transaction_iti21, self::$transaction_iti22),
            "PDS" => array_merge(self::$transaction_iti21, self::$transaction_iti22),
        ];

        if (array_key_exists($actor_name, $actors)) {
            return $actors[$actor_name];
        }

        return [];
    }
}
