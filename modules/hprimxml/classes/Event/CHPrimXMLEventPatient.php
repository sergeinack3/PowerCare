<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml\Event;

use Ox\Core\CAppUI;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Hprimxml\CHPrimXML;
use Ox\Interop\Hprimxml\CHPrimXMLDocument;
use Ox\Interop\Hprimxml\CHPrimXMLEvenementsPatients;

/**
 * Class CADM
 * Transfert de données d'admission
 */
class CHPrimXMLEventPatient extends CHPrimXML
{
    /** @var string[] Events */
    public static $evenements = [
        'enregistrementPatient' => "CHPrimXMLEnregistrementPatient",
        'fusionPatient'         => "CHPrimXMLFusionPatient",
        'venuePatient'          => "CHPrimXMLVenuePatient",
        'fusionVenue'           => "CHPrimXMLFusionVenue",
        'mouvementPatient'      => "CHPrimXMLMouvementPatient",
        'debiteursVenue'        => "CHPrimXMLDebiteursVenue",
    ];

    /** @var string[] Elements */
    public static $documentElements = [
        'evenementsPatients' => "CHPrimXMLEventPatient",
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        $this->type = "patients";

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
     * Get event
     *
     * @param string $messagePatient Message
     *
     * @return CHPrimXMLEvenementsPatients|void
     */
    public static function getHPrimXMLEvenements($messagePatient)
    {
        $version = CAppUI::conf('hprimxml evt_patients version');

        $hprimxmldoc = new CHPrimXMLDocument(
            "patients/v" . str_replace(".", "_", $version),
            CHPrimXMLEvenementsPatients::getVersionEvenementsPatients()
        );

        // Récupération des informations du message XML
        $hprimxmldoc->loadXML($messagePatient);

        $type    = $hprimxmldoc->getTypeEvenementPatient();
        $dom_evt = new CHPrimXMLEvenementsPatients();
        if ($type) {
            $dom_evt = new CHPrimXMLEventPatient::$evenements[$type];
        }
        $dom_evt->loadXML($messagePatient);

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

