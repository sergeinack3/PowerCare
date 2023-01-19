<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml\Event;

use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hprimxml\CHPrimXML;
use Ox\Interop\Hprimxml\CHPrimXMLEvenementsServeurActivitePmsi;

/**
 * Class CHPrimXMLEventServeurActivitePmsi
 *
 */
class CHPrimXMLEventServeurActivitePmsi extends CHPrimXML
{
    /** @var string[] Events */
    public static $evenements = [
        'evenementPMSI'                => "CHPrimXMLEvenementsPmsi",
        'evenementServeurActe'         => "CHPrimXMLEvenementsServeurActes",
        'patient'                      => "CHPrimXMLEvenementsServeurEtatsPatient",
        'evenementFraisDivers'         => "CHPrimXMLEvenementsFraisDivers",
        'evenementServeurIntervention' => "CHPrimXMLEvenementsServeurIntervention",
    ];

    /** @var string[] Elements */
    public static $documentElements = [
        'evenementsServeurActes'       => "CHPrimXMLEventServeurActivitePmsi",
        'evenementsPMSI'               => "CHPrimXMLEventServeurActivitePmsi",
        'evenementsFraisDivers'        => "CHPrimXMLEventServeurActivitePmsi",
        'evenementServeurIntervention' => "CHPrimXMLEventServeurActivitePmsi",
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->type = "pmsi";

        parent::__construct();
    }

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
     * Retrieve events list of data format
     *
     * @return array Events list
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @see parent::getHPrimXMLEvenements
     */
    public static function getHPrimXMLEvenements($messageServeurActivitePmsi)
    {
        $hprimxmldoc = new CMbXMLDocument();
        $hprimxmldoc->loadXML($messageServeurActivitePmsi);

        $xpath = new CMbXPath($hprimxmldoc);
        $event = $xpath->queryUniqueNode("/*/*[2]");

        $dom_evt = new CHPrimXMLEvenementsServeurActivitePmsi();
        if ($nodeName = $event->nodeName) {
            $dom_evt = new CHPrimXMLEventServeurActivitePmsi::$evenements[$nodeName];
        }

        $dom_evt->loadXML($messageServeurActivitePmsi);

        return $dom_evt;
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
    }
}
