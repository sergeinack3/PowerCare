<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Interop\Eai\CInteropNorm;

/**
 * Class CCDAEvent
 * Clinical Document Architecture
 */
class CCDAEvent extends CInteropNorm
{
    /** @var array Versions */
    public static $versions = [];

    /** @var string[] Events */
    public static $evenements = [
        // POCD_HD000040
        "POCD_HD000040" => "CCDAPOCD_HD000040",
    ];

    /** @var string[] Elements */
    public static $documentElements = [
        'ClinicalDocument' => "CCDAEvent",
    ];

    /**
     * Récupération des évènements disponibles
     *
     * @return array
     */
    public function getDocumentElements(): ?array
    {
        return self::$documentElements;
    }

    /**
     * Construct
     *
     */
    public function __construct()
    {
        $this->name   = "CCDA";
        $this->domain = self::DOMAIN_CDA;
        $this->type   = "CDA";

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
     * Get CDA event
     *
     * @param string $msg Message
     *
     * @return true|CCDADomDocument
     */
    public function getCDAEvent(string $msg): ?CCDADomDocument
    {
        $domCDA                     = new CCDADomDocument("ISO-8859-1");
        $domCDA->preserveWhiteSpace = false;
        $domCDA->loadXML($msg);

        $xpath  = new CCDAXPath($domCDA);
        $prefix = $xpath::PREFIX_NS;
        $typeId = $xpath->queryAttributNode("//$prefix:ClinicalDocument/$prefix:typeId", $domCDA->documentElement, "extension");

        $classname = "CCDA$typeId";
        if (class_exists($classname)) {
            /** @var CCDADomDocument $dom_evt */
            $dom_evt                     = new $classname("ISO-8859-1");
            $dom_evt->preserveWhiteSpace = false;
            $dom_evt->loadXML($msg);
            $dom_evt->msg_xml = $msg;

            return $dom_evt;
        }

        return null;
    }
}
