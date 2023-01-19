<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\SIHCabinet\CReceiverHL7v2SIHCabinet;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2SegmentPV1_FR 
 * PV1 - Represents an HL7 PV1 FR message segment (Patient Visit)
 */

class CHL7v2SegmentPV1_FR extends CHL7v2Segment {
  public $name   = "PV1";
  public $set_id;
  

  /** @var CSejour */
  public $sejour;
  

  /** @var CAffectation */
  public $curr_affectation;

  /**
   * Build PV1 (FR) segment
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $receiver = $event->_receiver;
    $group    = $receiver->_ref_group;

    $sejour           = $this->sejour;
    $curr_affectation = $this->curr_affectation;
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
    if (!$sejour || !$sejour->_id) {
      $data[] = "N";
      $this->fill($data);
      return;
    }

    $data[] = $sejour->_hdj_seance ? "R" : CHL7v2TableEntry::mapTo("4", $sejour->type);
    
    // PV1-3: Assigned Patient Location (PL) (optional)
    $data[] = $this->getPL($receiver, $sejour, $curr_affectation);
    
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
    if ($naissance->_id || $sejour->_naissance) {
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
    if ($receiver->_configs["build_PV1_5"] == "none") {
      $data[] = null;
    }
    elseif ($receiver->_configs["build_PV1_5"] == "sejour_id") {
      if (CHL7v2Message::$build_mode == "simple") {
        $data[] = array (
          $sejour->_id,
        );
      }
      else {
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
    }
    else if ($sejour->_etat != "preadmission" && $sejour->_admit !== true) {
      $data[] = null;
    }
    else {
      // PV1-5: Preadmit Number (CX) (optional)
      if (CHL7v2Message::$build_mode == "simple") {
        $data[] = array (
          $sejour->_id,
        );
      }
      else {
        // Même traitement que pour l'IPP
        switch ($receiver->_configs["build_PID_3_4"]) {
          case 'actor':
            $assigning_authority = $this->getAssigningAuthority("actor", null, $receiver);
            break;

          default:
            $assigning_authority = $this->getAssigningAuthority("FINESS", $group->finess);
            break;
        }

        $sejour->loadNPA($group->_id);
        $sejour->loadNDA($group->_id);

        $NPA    = $sejour->_NPA;
        $number = $NPA ? $NPA : $sejour->_NDA;

        $data[] = $number ? array(
          array(
            $number,
            null,
            null,
            // PID-3-4 Autorité d'affectation
            $assigning_authority,
            "AN"
          )
        ) : null;
      }
    }

    // PV1-6: Prior Patient Location (PL) (optional)
    $data[] = null;

    // PV1-7: Attending Doctor (XCN) (optional repeating)
    $praticien = $sejour->loadRefPraticien();
    if (isset($sejour->_ref_hl7_movement)) {
      $movement = $sejour->_ref_hl7_movement;
      $praticien = $movement->loadRefAffectation()->loadRefPraticien();
      if (!$praticien || !$praticien->_id) {
        $praticien = $sejour->_ref_praticien;
      }
    }
    $data[] = $this->getXCN($praticien, $receiver, true);
    
    // PV1-8: Referring Doctor (XCN) (optional repeating)
    $data[] = $sejour->adresse_par_prat_id ? $this->getXCN($sejour->loadRefAdresseParPraticien(), $receiver, true) : null;
    
    // PV1-9: Consulting Doctor (XCN) (optional repeating)
    $data[] = null;
    
    // PV1-10: Hospital Service (IS) (optional)
    $data[] = $sejour->discipline_id;
    
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
    $vip = $sejour->loadRefPatient()->vip;
    if ($event->message->extension && $event->message->extension > "FRA_2.5") {
      $data[] = CHL7v2TableEntry::mapTo("99", $vip);
    }
    else {
      $data[] = $vip ? "I" : "P";
    }

    // PV1-17: Admitting Doctor (XCN) (optional repeating)
    $data[] = $this->getXCN($sejour->loadRefPraticien(), $receiver, true);
    
    // PV1-18: Patient Type (IS) (optional)
    $data[] = null;
    
    // PV1-19: Visit Number (CX) (optional)
    $identifiers = array();
    if ($receiver->_configs["build_PV1_19"] == "simple") {
      $identifiers[] = $sejour->_NDA;
    }
    else {
      if ($receiver->_configs["build_NDA"] == "PV1_19") {
        $identifiers[] = $sejour->_NDA ? array(
          $sejour->_NDA,
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $this->getAssigningAuthority("FINESS", $group->finess),
          "AN"
        ) : array();
      }
      else {
        $idex = new CIdSante400();
        // On peut également passer l'identifiant externe du séjour
        if ($idex_tag = $receiver->_configs["build_PV1_19_idex_tag"]) {
          $idex = CIdSante400::getMatch($sejour->_class, $idex_tag, null, $sejour->_id);
        }

        /* @todo Gestion des séances */
        $identifiers[] = array(
          $idex->_id ? $idex->id400 : $sejour->_id,
          null,
          null,
          // PID-3-4 Autorité d'affectation
          $this->getAssigningAuthority($idex->_id ? "actor" : "mediboard", null, $receiver, null, $sejour->group_id),
          $receiver->_configs["build_PV1_19_identifier_authority"]
        );
      }

      // Ajout des identifiants des acteurs d'intégration
      $this->fillActorsIdentifiers($identifiers, $sejour, $receiver);
    }
    $data[] = $identifiers;
    
    // PV1-20: Financial Class (FC) (optional repeating)
    $data[] = $sejour->loadRefPrestation()->code;

    // PV1-21: Charge Price Indicator (IS) (optional)
    // Table - 0032
    $data[] = $this->getModeTraitement($receiver, $sejour, $naissance);

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
    $data[] = null;
    
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
    if ($sejour->annule &&
      ((CModule::getActive("appFineClient") && CAppFineClient::loadIdex($sejour)->_id && $receiver->_configs["send_evenement_to_mbdmp"])
        || ($receiver instanceof CReceiverHL7v2SIHCabinet))
    ) {
      // Table - 0111 - Delete Account Code
      // D - Admit cancel
      $data[] = CHL7v2TableEntry::mapTo("111", "d");
    }
    else {
      $data[] = null;
    }
    
    // PV1-35: Delete Account Date (DT) (optional)
    $data[] = null;
    
    // PV1-36: Discharge Disposition (IS) (optional)
    $sejour->loadRefsAffectations();
    $data[] = $this->getPV136($receiver, $sejour);
    
    // PV1-37: Discharged to Location (DLD) (optional)
    $data[] = ($sejour->etablissement_sortie_id &&
              ($event->code == "A03" || $event->code == "A16" || $event->code == "A21" || $event->code == "Z99")) ?
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
    if ($sejour->_hdj_seance && ($event->code == "A03" || $event->code == "Z99")) {
      $data[] = $sejour->last_seance ? "D" : "N";
    }
    else {
      $data[] = "";
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
    $data[] = null;
    
    // PV1-51: Visit Indicator (IS) (optional)
    $data[] = $sejour->_hdj_seance ? "V" : null;
    
    // PV1-52: Other Healthcare Provider (XCN) (optional repeating)
    $data[] = null;
    
    $this->fill($data);
  }
}
