<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentPV2
 * PV2 - Represents an HL7 PV2 message segment (Patient Visit - Additional Information)
 */

class CHL7v2SegmentPV2_RESP extends CHL7v2Segment {

  /** @var string */
  public $name   = "PV2";
  

  /** @var CSejour */
  public $sejour;
  

  /** @var COperation */
  public $operation;

  /**
   * Build PV2 segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    $data = array();
    
    $sejour   = $this->sejour;
    $receiver = $event->_receiver;
    
    parent::build($event);
    
    // PV2-1: Prior Pending Location (PL) (optional)
    $data[] = null;
    
    // PV2-2: Accommodation Code (CE) (optional)
    $data[] = null;
    
    // PV2-3: Admit Reason (Psychiatrie) (CE) (optional)
    // Table - 9000
    // HL  - Hospitalisation libre
    // HO  - Placement d'office
    // HDT - Hospitalisation � la demande d'un tiers
    $triggers = array("A01", "A05", "A06", "A14", "Z99");
    if ($sejour->type == "psy" && (in_array($event->code, $triggers))) {
      $data[] = CHL7v2TableEntry::mapTo("9000", $sejour->modalite);
    }
    else {
      $data[] = null;
    }
    
    // PV2-4: Transfer Reason (CE) (optional)
    $data[] = null;
    
    // PV2-5: Patient Valuables (ST) (optional repeating)
    $data[] = null;
    
    // PV2-6: Patient Valuables Location (ST) (optional)
    $data[] = null;
    
    // PV2-7: Visit User Code (IS) (optional repeating)
    // Table - 0130
    // TN - Nouveau m�decin traitant (le patient a chang� de m�decin traitant ou d�clar� ce m�decin pour la 1�re fois)
    // TD - acc�s direct sp�cifique
    // TU - urgence: (le patient arrive aux urgences, sans recommandation du m�decin traitant)
    // TH - hors r�sidence habituelle
    // TR - le patient est envoy� par le rempla�ant du m�decin traitant
    // MR - M�decin consult� = rempla�ant du m�decin traitant
    // TO - patient orient� par le m�decin traitant (le patient consulte un autre m�decin sur conseil du m�decin traitant)
    // ME - consultation du m�decin traitant = m�decin consult�
    // 1V - 1�re consultation du m�decin traitant pour avis
    // IT - soins it�ratifs en accord avec le m�decin traitant (D162-1-6 Alin�as 1 ou 2)
    // AG - le patient a moins de 16 ans au moment de la consultation (Pas de code B2)
    // MT - le patient est envoy� par le m�decin du travail de l'h�pital (Pas de code B2)
    // CS - acc�s hors coordination (acces sur initiative du patient sans consultation du m�decin traitant)
    // SM - le patient n'a pas de m�decin traitant
    // ML - Militaire sur prescription m�dicale des arm�es (Art D162-1-6 SS) (patient non envoy� par le m�decin traitant)
    // EM - Exclusion m�dicale (tabagisme, alcoolisme, ..) (Art D162-1-6 SS) (patient non envoy� par le m�decin traitant)
    // NT - Le patient est orient� par un m�decin qui n'est pas son m�decin traitant
    // PI - L'ex�cutant est un m�decin g�n�raliste primo install� r�cemment
    // ZD - L'ex�cutant est un m�decin g�n�raliste s'installant en zone m�dicalement d�ficitaire
    // AL - Actes et consultations pr�vus dans le cadre du protocole de soins ALD D162-1-6 Alin�a 3
    // PS - Actes et consultations intervenant dans le cadre de la permanence de soins ALD D162-1-6 Alin�a 5
    // AM - Aide m�dicale d'�tat (AME) (Pas de code B2) 
    // CI - Etranger pris en charge dans le cadre de conventions internationales (Pas de code B2)
    // ET - Etranger pris en charge - autres situations (situation r�guli�re)
    // MI - Migrants de passage (L254-1)
    // DT - Parcours de soins non actif (parcours de soins d�but� avant la date d'application de la r�glementation)
    // MA - Cas particulier de la caisse de Mayotte
    // AS - Autre situation
    $data[] = "TN";

    // PV2-8: Expected Admit Date/Time (TS) (optional)
    $data[] = $sejour->entree_prevue;
    
    // PV2-9: Expected Discharge Date/Time (TS) (optional)
    $data[] = $sejour->sortie_prevue;
    
    // PV2-10: Estimated Length of Inpatient Stay (NM) (optional)
    $data[] = null;
    
    // PV2-11: Actual Length of Inpatient Stay (NM) (optional)
    $data[] = null;
    
    // PV2-12: Visit Description (ST) (optional)
    $data[] = $sejour->libelle;
    
    // PV2-13: Referral Source Code (XCN) (optional repeating)
    $data[] = null;
    
    // PV2-14: Previous Service Date (DT) (optional)
    $data[] = null;
    
    // PV2-15: Employment Illness Related Indicator (ID) (optional)
    $data[] = null;
    
    // PV2-16: Purge Status Code (IS) (optional)
    $data[] = null;
    
    // PV2-17: Purge Status Date (DT) (optional)
    $data[] = null;
    
    // PV2-18: Special Program Code (IS) (optional)
    $data[] = null;
    
    // PV2-19: Retention Indicator (ID) (optional)
    $data[] = null;
    
    // PV2-20: Expected Number of Insurance Plans (NM) (optional)
    $data[] = null;
    
    // PV2-21: Visit Publicity Code (IS) (optional)
    $data[] = null;
    
    // PV2-22: Visit Protection Indicator (ID) (optional)
    // Table - 0136
    // Y - Oui - Acc�s prot�g� � l'information du patient
    // N - Non - Acc�s normal � l'information du patient
    $data[] = ($sejour->loadRefPatient()->vip) ? "Y" : "N";
    
    // PV2-23: Clinic Organization Name (XON) (optional repeating)
    $data[] = null;
    
    // PV2-24: Patient Status Code (IS) (optional)
    $data[] = null;
    
    // PV2-25: Visit Priority Code (IS) (optional)
    $data[] = null;
    
    // PV2-26: Previous Treatment Date (DT) (optional)
    $data[] = null;
    
    // PV2-27: Expected Discharge Disposition (IS) (optional)
    $data[] = null;
    
    // PV2-28: Signature on File Date (DT) (optional)
    $data[] = null;
    
    // PV2-29: First Similar Illness Date (DT) (optional)
    $data[] = null;
    
    // PV2-30: Patient Charge Adjustment Code (CE) (optional)
    $data[] = null;
    
    // PV2-31: Recurring Service Code (IS) (optional)
    $data[] = null;
    
    // PV2-32: Billing Media Code (ID) (optional)
    $data[] = null;
    
    // PV2-33: Expected Surgery Date and Time (TS) (optional)
    $data[] = null;
    
    // PV2-34: Military Partnership Code (ID) (optional)
    $data[] = null;
    
    // PV2-35: Military Non-Availability Code (ID) (optional)
    $data[] = null;
    
    // PV2-36: Newborn Baby Indicator (ID) (optional)
    $data[] = null;
    
    // PV2-37: Baby Detained Indicator (ID) (optional)
    $data[] = null;
    
    // PV2-38: Mode of Arrival Code (CE) (optional)
    // Table - 0430
    // 0 - Police
    // 1 - SAMU, SMUR terrestre
    // 2 - Ambulance publique
    // 3 - Ambulance priv�e
    // 4 - Taxi
    // 5 - Moyens personnels
    // 6 - SAMU, SMUR h�licopt�re
    // 7 - Pompier
    // 8 - VSL
    // 9 - Autre
    if ($sejour->type == "urg") {
      $data[] = CHL7v2TableEntry::mapTo("0430", $sejour->transport);
    }
    else {
      $data[] = null;
    }
    
    // PV2-39: Recreational Drug Use Code (CE) (optional repeating)
    $data[] = null;
    
    // PV2-40: Admission Level of Care Code (CE) (optional)
    $data[] = null;
    
    // PV2-41: Precaution Code (CE) (optional repeating)
    $data[] = null;
    
    // PV2-42: Patient Condition Code (CE) (optional)
    $data[] = null;
    
    // PV2-43: Living Will Code (IS) (optional)
    $data[] = null;
    
    // PV2-44: Organ Donor Code (IS) (optional)
    $data[] = null;
    
    // PV2-45: Advance Directive Code (CE) (optional repeating)
    $data[] = null;
    
    // PV2-46: Patient Status Effective Date (DT) (optional)
    $data[] = null;
    
    // PV2-47: Expected LOA Return Date/Time (TS) (optional)
    $data[] = null;
    
    // PV2-48: Expected Pre-admission Testing Date/Time (TS) (optional)
    $data[] = null;
    
    // PV2-49: Notify Clergy Code (IS) (optional repeating)
    $data[] = null;
    
    $this->fill($data);
  }
}