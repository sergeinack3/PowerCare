<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CHL7v3AcknowledgmentPRPA;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3AcknowledgmentXDSb;
use Ox\Interop\Xds\CXDSXmlDocument;

/**
 * Class CXDS
 * Cross-Enterprise Document Sharing
 */
class CXDSb extends CIHE
{
    /** @var array */
    public static $interaction_ITI18 = [
        // Patient Registry Get Demographics Query
        "RegistryStoredQuery",
    ];

    /** @var array */
    public static $interaction_ITI41 = [
        // Patient Registry Get Demographics Query
        "ProvideAndRegisterDocumentSetRequest",
    ];

    public static $interaction_ITI43 = [
        "RetrieveDocumentSet",
    ];

    /** @var array */
    public static $interaction_ITI57 = [
        // Patient Registry Get Demographics Query
        "UpdateDocumentSet",
    ];

    /** @var array */
    public static $evenements = [
        // Patient Registry Get Demographics Query
        "ProvideAndRegisterDocumentSetRequest" => "CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest",

        "RegistryStoredQuery" => "CHL7v3EventXDSbRegistryStoredQuery",
        "UpdateDocumentSet"   => "CHL7v3EventXDSbUpdateDocumentSet",
        "RetrieveDocumentSet" => "CHL7v3EventXDSbRetrieveDocumentSet",
    ];

    /**
     * Construct
     *
     * @return CXDSb
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "XDSb";

        $this->_categories = [
            "ITI-41" => self::$interaction_ITI41,
            "ITI-18" => self::$interaction_ITI18,
            "ITI-57" => self::$interaction_ITI57,
            "ITI-43" => self::$interaction_ITI43,
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
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return CHL7Event An instance of data format
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
        $classname = "CHL7v3Event{$exchange->type}{$exchange->sous_type}";

        return new $classname();
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
        if (in_array($code, self::$interaction_ITI18)) {
            return "ITI18";
        }

        if (in_array($code, self::$interaction_ITI41)) {
            return "ITI41";
        }

        if (in_array($code, self::$interaction_ITI43)) {
            return "ITI43";
        }

        if (in_array($code, self::$interaction_ITI57)) {
            return "ITI57";
        }

        return null;
    }

    /**
     * Get aAcknowledgment object
     *
     * @param string $ack_data Data
     *
     * @return CHL7v3AcknowledgmentPRPA|null
     */
    public static function getAcknowledgment(string $ack_data): ?CHL7Event
    {
        $dom = new CXDSXmlDocument();
        $dom->loadXML($ack_data);

        $element   = $dom->documentElement;
        $localName = $element->localName;

        $name_event = str_replace("Response", "", $localName);

        $class_name = "CHL7v3Acknowledgment$name_event";

        switch ($class_name) {
            case "CHL7v3AcknowledgmentRetrieveDocumentSet":
                $hl7event = new $class_name();
                break;
            default:
                $hl7event = new CHL7v3AcknowledgmentXDSb();
        }

        $hl7event->dom = $dom;

        return $hl7event;
    }
}
