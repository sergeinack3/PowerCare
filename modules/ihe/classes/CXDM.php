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
 * Class CXDM
 * Cross-Enterprise Document Media Interchange (XDM)
 */
class CXDM extends CIHE
{
    /** @var array */
    public static $interaction_ITI32 = [
        // Distribute Document Set on Media
        "DistributeDocumentSetOnMedia",
    ];

    /** @var array */
    public static $evenements = [
        "DistributeDocumentSetOnMedia" => "CHL7v3EventXDMDistributeDocumentSetOnMedia",
    ];

    /**
     * Construct
     *
     * @return CXDSb
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "XDM";

        $this->_categories = [
            "ITI-32" => self::$interaction_ITI32,
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
        if (in_array($code, self::$interaction_ITI32)) {
            return "ITI32";
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
            case "CHL7v3AcknowledgmentDistributeDocumentSetOnMedia":
                $hl7event = new $class_name();
                break;
            default:
                $hl7event = new CHL7v3AcknowledgmentXDSb();
        }

        $hl7event->dom = $dom;

        return $hl7event;
    }
}
