<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use DOMElement;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hl7\CHL7v3AcknowledgmentPRPA;
use Ox\Interop\Hl7\CHL7v3MessageXML;
use Ox\Interop\Hl7\CHL7v3Messaging;

/**
 * Class CPRPA
 * Patient Administration
 */
class CPRPA extends CHL7v3Messaging
{
    /** @var array */
    public static $versions = [
        "2008",
        "2009",
    ];

    /** @var array */
    public static $interaction_ST201317UV = [
        // Patient Registry Find Candidates Query
        "IN201305UV02",
        "IN201306UV02",
        // Patient Registry Get Demographics Query
        "IN201307UV02",
        "IN201308UV02",
        // Patient Registry AddPatient
        "IN201311UV02",
        "IN201312UV02",
        "IN201313UV02",
        // Patient Registry Request Add Patient
        "IN201314UV02",
        "IN201315UV02",
        "IN201316UV02",
    ];

    /** @var array */
    public static $evenements = [
        // Patient Registry Find Candidates Query
        "IN201305UV02" => "CHL7v3EventPRPAIN201305UV02",
        // Patient Registry Find Candidates Query Response
        "IN201306UV02" => "CHL7v3EventPRPAIN201306UV02",
        // Patient Registry Get Demographics Query
        "IN201307UV02" => "CHL7v3EventPRPAIN201307UV02",
        // Patient Registry Get Demographics Query Response
        "IN201308UV02" => "CHL7v3EventPRPAIN201308UV02",

        // Patient Registry AddPatient
        "IN201311UV02" => "CHL7v3EventPRPAIN201311UV02",
        // Patient Registry Request Added
        "IN201312UV02" => "CHL7v3EventPRPAIN201312UV02",
        // Patient Registry Request Not Added
        "IN201313UV02" => "CHL7v3EventPRPAIN201313UV02",

        // Patient Registry Request Add Patient
        "IN201314UV02" => "CHL7v3EventPRPAIN201314UV02",
        // Patient Registry Add Request Accepted
        "IN201315UV02" => "CHL7v3EventPRPAIN201315UV02",
        // Patient Registry Add Request Rejected
        "IN201316UV02" => "CHL7v3EventPRPAIN201316UV02",
    ];

    /**
     * Construct
     *
     * @return CPRPA
     */
    public function __construct()
    {
        $this->domain = self::DOMAIN_ITI;
        $this->type   = "PRPA";

        $this->_categories = [
            "ST201317UV" => self::$interaction_ST201317UV,
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
        $classname = "CHL7v3EventPRPA{$exchange->sous_type}";

        return new $classname();
    }

    /**
     * Get aAcknowledgment object
     *
     * @param string $ack_data Data
     *
     * @return CHL7v3AcknowledgmentPRPA|null
     */
    static function getAcknowledgment(string $ack_data)
    {
        $dom = new CHL7v3MessageXML();
        $dom->loadXML($ack_data);

        $element = $dom->documentElement;
        $tagName = $element->tagName;

        if (strpos($tagName, "_Response") !== false) {
            $first_node = $dom->firstChild;
            $element    = $dom->documentElement;
            $tagName    = $element->tagName;

            foreach ($first_node->childNodes as $_node) {
                if ($_node instanceof DOMElement) {
                    $tagName = $_node->tagName;
                    break;
                }
            }
        }

        $first_element = str_replace("PRPA_", "", $tagName);

        if (array_key_exists($first_element, self::$evenements)) {
            $dom->hl7v3_version = "2009";
            $dom->dirschemaname = $tagName;

            $hl7event = new self::$evenements[$first_element];
            $hl7event->dom = $dom;

            return $hl7event;
        }

        return null;
    }
}
