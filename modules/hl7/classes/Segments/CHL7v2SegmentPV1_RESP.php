<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CAppUI;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentPV1 
 * PV1 - Represents an HL7 PV1 message segment (Patient Visit)
 */

class CHL7v2SegmentPV1_RESP extends CHL7v2Segment {

  /** @var string */
  public $name   = "PV1";

  /** @var null */
  public $set_id;
  

  /** @var CSejour */
  public $sejour;
  

  /** @var CAffectation */
  public $curr_affectation;

  /**
   * Build PV1 segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $receiver = $event->_receiver;

    $sender   = $event->_sender;
    $group    = $sender->_ref_group;
    
    $sejour  = $this->sejour;
    
    $data = array();

    // PV1-1: Set ID - PV1 (SI) (optional)
    $data[] = $this->set_id;
    
    // PV1-2: Patient Class (IS)
    // Table - 0004
    // E - Emergency - Passage aux Urgences - Arrivée aux urgences
    // I - Inpatient - Hospitalisation
    // N - Not Applicable - Non applicable - 
    // O - Outpatient - Actes et consultation externe
    // R - Recurring patient - Séances
    // Cas de la transaction ITI-30 "Patient Identity Field"
    if (!$sejour) {
      $data[] = "N";
      $this->fill($data);
      return;
    } 
    $data[] = CHL7v2TableEntry::mapTo("4", $sejour->type);
    
    // PV1-3: Assigned Patient Location (PL) (optional)
    $data[] = $this->getPL($receiver, $sejour, $this->curr_affectation);

    // PV1-4: Admission Type (IS) (optional)
    // Table - 0007
    // C  - Confort (chirurgie esthétique)
    // L  - Accouchement maternité
    // N  - Nouveau né
    // R  - Routine (par défaut)
    // U  - Caractère d'urgence aigue du problème quel que soit le service d'entrée
    // RM - Rétrocession du médicament
    // IE - Prestation inter-établissements
    $naissance = new CNaissance();
    $naissance->sejour_enfant_id = $sejour->_id;
    $naissance->loadMatchingObject();
    // Cas d'une naissance
    if ($naissance->_id) {
      $data[] = "N";
    }
    // Cas accouchement maternité
    elseif ($sejour->type_pec == "O") {
      $data[] = "L";
    }
    // Défaut
    else {
      $data[] = "R";
    }

    // PV1-5: Preadmit Number (CX) (optional)
    if (CHL7v2Message::$build_mode == "simple") {
      $data[] = array (
        $sejour->_id,
      );
    }
    else {
      $sejour->loadNPA($group->_id);
      $data[] = $sejour->_NPA ? array(
                  array(
                    $sejour->_NPA,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $this->getAssigningAuthority("FINESS", $group->finess),
                    "RI"
                  )
                ) : null;
    }

    // PV1-6: Prior Patient Location (PL) (optional)
    $data[] = $this->getPreviousPL($receiver, $sejour);

    // PV1-7: Attending Doctor (XCN) (optional repeating)
    $sejour->loadRefPraticien();
    $data[] = $this->getXCN($sejour->_ref_praticien, $receiver);

    // PV1-8: Referring Doctor (XCN) (optional repeating)
    $data[] = $sejour->adresse_par_prat_id ? $this->getXCN($sejour->loadRefAdresseParPraticien(), $receiver) : null;

    // PV1-9: Consulting Doctor (XCN) (optional repeating)
    $data[] = null;

    // PV1-10: Hospital Service (IS) (optional)
    $data[] = $this->getPV110($receiver, $sejour, $this->curr_affectation);

    // PV1-11: Temporary Location (PL) (optional)
    $data[] = null;

    // PV1-12: Preadmit Test Indicator (IS) (optional)
    $data[] = null;

    // PV1-13: Re-admission Indicator (IS) (optional)
    $data[] = null;

    // PV1-14: Admit Source (IS) (optional)
    $data[] = $this->getPV114($receiver, $sejour);

    // PV1-15: Ambulatory Status (IS) (optional repeating)
    $data[] = null;

    // PV1-16: VIP Indicator (IS) (optional)
    // Table - 0099
    // P - Public
    // I - Incognito
    $data[] = $sejour->loadRefPatient()->vip ? "I" : "P";

    // PV1-17: Admitting Doctor (XCN) (optional repeating)
    $data[] = $this->getXCN($sejour->_ref_praticien, $receiver);

    // PV1-18: Patient Type (IS) (optional)
    $data[] = null;

    // PV1-19: Visit Number (CX) (optional)
    if (!empty($receiver->_configs["build_NDA"]) && ($receiver->_configs["build_NDA"] == "PV1_19")) {
      $sejour->loadNDA($group->_id);
      $data[] = $sejour->_NDA ? array(
                  array(
                    $sejour->_NDA,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $this->getAssigningAuthority("FINESS", $group->finess),
                    "AN"
                  )
                ) : null;
    }
    else {
      /* @todo Gestion des séances */
      $data[] = array(
        array (
          $sejour->_id,
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $this->getAssigningAuthority("mediboard", null, null, null, $sejour->group_id),
          "RI"
        )
      );
    }

    // PV1-20: Financial Class (FC) (optional repeating)
    $data[] = $sejour->loadRefPrestation()->code;

    // PV1-21: Charge Price Indicator (IS) (optional)
    // Table - 0032
    $data[] = $sejour->loadRefChargePriceIndicator()->code;

    // PV1-22: Courtesy Code (IS) (optional)
    // Table - 0045
    // Y - Demande de chambre particulière
    // N - Pas de demande de chambre particulière
    $data[] = $sejour->chambre_seule ? "Y" : "N";

    // PV1-23: Credit Rating (IS) (optional)
    $data[] = null;

    // PV1-24: Contract Code (IS) (optional repeating)
    $data[] = null;

    // PV1-25: Contract Effective Date (DT) (optional repeating)
    $data[] = null;

    // PV1-26: Contract Amount (NM) (optional repeating)
    $data[] = $this->getPV126($receiver, $sejour);

    // PV1-27: Contract Period (NM) (optional repeating)
    $data[] = null;

    // PV1-28: Interest Code (IS) (optional)
    $data[] = null;

    // PV1-29: Transfer to Bad Debt Code (IS) (optional)
    $data[] = null;

    // PV1-30: Transfer to Bad Debt Date (DT) (optional)
    $data[] = null;

    // PV1-31: Bad Debt Agency Code (IS) (optional)
    $data[] = null;

    // PV1-32: Bad Debt Transfer Amount (NM) (optional)
    $data[] = null;

    // PV1-33: Bad Debt Recovery Amount (NM) (optional)
    $data[] = null;

    // PV1-34: Delete Account Indicator (IS) (optional)
    $data[] = null;

    // PV1-35: Delete Account Date (DT) (optional)
    $data[] = null;

    // PV1-36: Discharge Disposition (IS) (optional)
    $sejour->loadRefsAffectations();
    $data[] = $this->getPV136($receiver, $sejour);
    
    // PV1-37: Discharged to Location (DLD) (optional)
    $data[] = ($sejour->etablissement_sortie_id && ($event->code == "A03" || $event->code == "A16" || $event->code == "A21")) ?
      array($sejour->loadRefEtablissementTransfert()->finess) : null;
    
    // PV1-38: Diet Type (CE) (optional)
    $data[] = null;
    
    // PV1-39: Servicing Facility (IS) (optional)
    $data[] = null;
    
    // PV1-40: Bed Status (IS) (optional)
    // Interdit par IHE France
    $data[] = null;
    
    // PV1-41: Account Status (IS) (optional)
    // Utilisation que pour les événements A03 et Z99
    // Table - 0117
    // D - C'était la dernière venue pour ce dossier administratif
    // N - Ce n'était pas la dernière venue pour ce dossier administratif
    if ($event->code == "A03" || $event->code == "Z99") {
      $data[] = ($sejour->type != "seances" && $sejour->sortie_reelle) ? "D" : "N";
    }
    else {
      $data[] = null;
    }
    
    // PV1-42: Pending Location (PL) (optional)
    $data[] = null;
    
    // PV1-43: Prior Temporary Location (PL) (optional)
    $data[] = null;
    
    // PV1-44: Admit Date/Time (TS) (optional)
    $data[] = $sejour->entree_reelle;
    
    // PV1-45: Discharge Date/Time (TS) (optional repeating)
    $data[] = $sejour->sortie_reelle;
    
    // PV1-46: Current Patient Balance (NM) (optional)
    $data[] = null;
    
    // PV1-47: Total Charges (NM) (optional)
    $data[] = null;
    
    // PV1-48: Total Adjustments (NM) (optional)
    $data[] = null;
    
    // PV1-49: Total Payments (NM) (optional)
    $data[] = null;
    
    // PV1-50: Alternate Visit ID (CX) (optional)
    // Cas de l'utilisation du rang
    if (CAppUI::conf("dPplanningOp CSejour use_dossier_rang")) {
      $sejour->loadNRA($group->_id);
      if (CHL7v2Message::$build_mode == "simple") {
        $data[] = array (
          $sejour->_ref_NRA->id400,
        );
      } 
    }
    else {
      $data[] = null;
    }
    
    // PV1-51: Visit Indicator (IS) (optional)
    $data[] = null;
    
    // PV1-52: Other Healthcare Provider (XCN) (optional repeating)
    $data[] = null;
    
    $this->fill($data);
  }
}