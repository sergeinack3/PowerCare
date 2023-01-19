<?php
/** @noinspection PhpUndefinedClassInspection */

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
 * Class CDEC
 * Device Enterprise Communication
 */
class CDEC extends CIHE
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
    public static $transaction_pcdO1 = [
        "R01",
    ];

    /**
     * @var array
     */
    public static $evenements = [
        // PDC-01
        "R01" => "CHL7EventORUR01",
    ];

    /**
     * Construct
     *
     * @return CDEC
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_PCD;
        $this->type   = "DEC";

        $this->_categories = [
            "PDC-01" => self::$transaction_pcdO1,
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
        if (in_array($code, self::$transaction_pcdO1)) {
            return "PCD01";
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
                $classname = "CHL7{$_version}EventORU$code";

                return new $classname();
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
            "DEV_OBS_CONSUMER" => self::$transaction_pcdO1,
        ];

        if (array_key_exists($actor_name, $actors)) {
            return $actors[$actor_name];
        }

        return [];
    }
}
