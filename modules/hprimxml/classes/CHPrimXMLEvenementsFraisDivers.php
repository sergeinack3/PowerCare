<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLEvenementsFraisDivers
 * Frais divers
 */
class CHPrimXMLEvenementsFraisDivers extends CHPrimXMLEvenementsServeurActivitePmsi {
  /**
   * Construct
   *
   * @return CHPrimXMLEvenementsFraisDivers
   */
  function __construct() {
    $this->sous_type = "evenementFraisDivers";
    $this->evenement = "evt_frais_divers";
    
    parent::__construct("evenementFraisDivers", "msgEvenementsFraisDivers");
  }

  /**
   * @inheritdoc
   */
  function generateEnteteMessage($type = null, $version = true , $group_id = null) {
    parent::generateEnteteMessage("evenementsFraisDivers");
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $mbSejour, $referent = false) {
    /** @var CSejour $mbSejour */
    $evenementsFraisDivers = $this->documentElement;
    $evenementFraisDivers  = $this->addElement($evenementsFraisDivers, "evenementFraisDivers");

    if (CModule::getActive("appFine")) {
      $receiver = $this->_ref_receiver;

      if ($receiver->_configs["build_frais_divers"] == "presta") {
        CAppFineServer::generateFromOperation($this, $evenementFraisDivers, $mbSejour);
      }
    }
    else {
      // Ajout du patient (light)
      $mbPatient = $mbSejour->_ref_patient;
      $patient = $this->addElement($evenementFraisDivers, "patient");
      $this->addPatient($patient, $mbPatient, false, true);

      // Ajout de la venue, c'est-à-dire le séjour (light)
      $venue = $this->addElement($evenementFraisDivers, "venue");
      $this->addVenue($venue, $mbSejour, false, true);

      $receiver = $this->_ref_receiver;
      if ($receiver->_configs["build_frais_divers"] == "fd") {
        // Ajout des frais divers
        foreach ($mbSejour->loadRefsFraisDivers() as $_mb_frais_divers) {
          $_mb_frais_divers->loadRefType();
          $_mb_frais_divers->loadRefExecutant();
          $_mb_frais_divers->loadExecution();

          $this->addFraisDivers($evenementFraisDivers, $_mb_frais_divers);
        }

        if ($mbSejour->_ref_consultations) {
          foreach ($mbSejour->_ref_consultations as $_consultation) {
            foreach ($_consultation->_ref_frais_divers as $_mb_frais_divers) {
              $_mb_frais_divers->loadRefType();
              $_mb_frais_divers->loadExecution();
              $_mb_frais_divers->loadRefExecutant();

              $this->addFraisDivers($evenementFraisDivers, $_mb_frais_divers);
            }
          }
        }
      }

      if ($receiver->_configs["build_frais_divers"] == "presta") {
        // Ajout des prestations
        $prestas = $mbSejour->getPrestations();

        // Parcours les jours
        foreach ($prestas as $date => $_presta) {
          // Parcours les prestas par jour
          foreach ($_presta as $_item_presta) {
            $this->addFraisDiversPrestas($evenementFraisDivers, $_item_presta, $date);
          }
        }
      }
    }
        
    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * Get content XML
   *
   * @return array
   */
  public function getContentsXML(): array
  {
      $data  = [];
      $xpath = new CHPrimXPath($this);

      $evenementFraisDivers = $xpath->queryUniqueNode("/hprim:evenementsFraisDivers/hprim:evenementFraisDivers");

      $data['patient']         = $xpath->queryUniqueNode("hprim:patient", $evenementFraisDivers);
      $data['idSourcePatient'] = $this->getIdSource($data['patient']);
      $data['idCiblePatient']  = $this->getIdCible($data['patient']);

      $data['venue']           = $xpath->queryUniqueNode("hprim:venue", $evenementFraisDivers);
      $data['idSourceVenue']   = $this->getIdSource($data['venue']);
    $data['idCibleVenue']    = $this->getIdCible($data['venue']);

    $data['FraisDivers']     = $xpath->query("hprim:FraisDivers", $evenementFraisDivers);

    return $data; 
  }

  /**
   * Enregistrement des frais divers / prestations
   *
   * @param CHPrimXMLAcquittements $dom_acq  DOM Acquittement
   * @param CMbObject              $mbObject Object
   * @param array                  $data     Data that contain the nodes
   *
   * @return string Acquittement
   **/
  function handle(CHPrimXMLAcquittements $dom_acq, CMbObject $mbObject, $data) {
    /** @var COperation $mbObject */
    $exchange_hprim = $this->_ref_echange_hprim;
    $sender         = $exchange_hprim->_ref_sender;
    $sender->loadConfigValues();

    $this->_ref_sender = $sender;

    $systeme_presta = CAppUI::conf("dPhospi prestations systeme_prestations", "CGroups-".$sender->group_id);
    if ($systeme_presta == "standard") {
      return $exchange_hprim->setAckError($dom_acq, "E501", null, $mbObject, $data);
    }

    if (CModule::getActive("appFineClient") && $sender->_configs["handle_appFine"] && $sender->_configs["frais_divers"] == "presta") {
      return CAppFineClient::mappingPrestation($data, $dom_acq, $exchange_hprim, $sender, $this);
    }

    // Acquittement d'erreur : identifiants source du patient / séjour non fournis
    if (!$data['idSourcePatient'] || !$data['idSourceVenue']) {
      return $exchange_hprim->setAckError($dom_acq, "E206", null, $mbObject, $data);
    }

    // IPP non connu => message d'erreur
    $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $data['idSourcePatient']);
    if (!$IPP->_id) {
      return $exchange_hprim->setAckError($dom_acq, "E013", null, $mbObject, $data);
    }

    // Chargement du patient
    $patient = new CPatient();
    $patient->load($IPP->object_id);

    // Num dossier non connu => message d'erreur
    $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $data['idSourceVenue']);
    if (!$NDA->_id) {
      return $exchange_hprim->setAckError($dom_acq, "E014", null, $mbObject, $data);
    }

    // Chargement du séjour
    $sejour = new CSejour();
    $sejour->load($NDA->object_id);

    // Si patient H'XML est différent du séjour
    if ($sejour->patient_id != $patient->_id) {
      return $exchange_hprim->setAckError($dom_acq, "E015", null, $sejour, $data);
    }

    // Chargement du patient du séjour
    $sejour->loadRefPatient();

    $warning = null;
    $comment = null;
    $codes   = array();

    switch ($sender->_configs["frais_divers"]) {
      case "presta":
        $prestations = array();
        $warning     = false;
        foreach ($data['FraisDivers'] as $_frais_divers) {
          if (!$this->mappingPrestation($_frais_divers, $sejour, $prestations, $warning)) {
            $warning = true;
          }
        }

        $data["prestations"] = $prestations;

        break;

      case "fd": default:
        return $exchange_hprim->setAckError($dom_acq, "E502", null, $sejour, $data);
    }

    return $exchange_hprim->setAck($dom_acq, $codes, $warning, $comment, $sejour, $data);
  }
}
