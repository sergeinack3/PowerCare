<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Segments;

use Ox\Core\CMbArray;
use Ox\Core\CMbString;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hprimsante\CHPrimSanteSegment;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHPrimSanteSegmentP
 * P - Represents an HPR P message segment (Message Patient)
 */
class CHPrimSanteSegmentP extends CHPrimSanteSegment {
  public $name = "P";

  /** @var CPatient */
  public $patient;

  /** @var CSejour */
  public $sejour;

  /**
   * @inheritdoc
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    $patient = $this->patient;
    $sejour  = $this->sejour;
    $actor   = (isset($event->_sender->_id)) ? $event->_sender : $event->_receiver;
    $group   = $actor->loadRefGroup();
    $patient->loadIPP($group->_id);

    // P-1: type segment - P(par défaut) (ST)
    // P-2: rang segment - (NM)
    $data[] = "1";

    // P-3: Patient ID (SPEC) (optional)
    $data[] = array(
      array(
        $patient->_IPP,
        $patient->_patient_elimine ? $patient->_patient_elimine->_IPP : null,
        $patient->_patient_elimine ? "FU" : null,
      )
    );

    // P-4: Patient ID (ST) (optional)
    $data[] = null;

    // P-5: sejour ID (ST) (optional)
    $data[] = null;

    // P-6: Nom patient (PN) (optional)
    $data[] = array(
      array(
        $patient->_p_last_name,
        $patient->_p_first_name,
        $patient->_prenom_2,
        $patient->civilite,
      )
    );

    // P-7: Nom de naissance (ST) (optional)
    $data[] = $patient->_p_maiden_name;

    // P-8: Date de naissance (TS) (optional)
    $data[] = $patient->_p_birth_date;

    // P-9: Sexe (ID) (optional)
    $data[] = CMbString::upper($patient->sexe);

    // P-10: race (forbidden)
    $data[] = null;
    $address = explode("\n", $patient->_p_street_address, 1);
    // P-11: adresse (AD) (optional)
    $data[] = array(
      array(
        CMbArray::get($address, 0),
        str_replace("\n", " ", CMbArray::get($address, 1)),
        $patient->_p_city,
        null,
        $patient->_p_postal_code,
        $patient->pays_insee
      )
    );

    // P-12: INS (version 2.3) (optional)
    $data[] = null;

    // P-13: téléphone (TN) (optional) (repeatable)
    $data[] = array($patient->_p_phone_number, $patient->_p_mobile_phone_number);

    // P-14: Médecins (CNA) (optional) (repeatable)
    $data[] = null;

    // P-15: Traitement local 1 (ST) (optional)
    $data[] = null;

    // P-16: Traitement local 2 (ST) (optional)
    $data[] = null;

    // P-17: Taille (CQ) (optional)
    $data[] = null;

    // P-18: Poids (CQ) (optional)
    $data[] = null;

    // P-19: Diagnostic (CE) (optional) (repeatable)
    $data[] = null;

    // P-20: Traitement (ST) (optional) (repeatable)
    $data[] = null;

    // P-21: Régime (ST) (optional)
    $data[] = null;

    // P-22: Commentaire 1 (ST) (optional)
    $data[] = null;

    // P-23: Commentaire 2 (ST) (optional)
    $data[] = null;

    // P-24: Date de mouvement (TS) (optional) (repeatable)
    $data[] = null;

    // P-25: Statut de l'admission (ID) (optional)
    $data[] = null;

    // P-26: Localisation (SPEC) (optional)
    $data[] = null;

    // P-27: classification diagnostic (CE) (optional)
    $data[] = null;

    // P-28: Religion (forbidden)
    $data[] = null;

    // P-29: Situation maritale (ID) (optional)
    $data[] = $this->getMaritalStatus($patient->situation_famille);

    // P-30: Précauton à prendre (ID) (optional)
    $data[] = null;

    // P-31: Langue (ST) (optional)
    $data[] = null;

    // P-32: Statut de confidentialité (ID) (optional)
    $data[] = null;

    // P-33: Date de dernière modification (TS) (optional)
    $data[] = null;

    // P-34: Date de décès (TS) (optional)
    $data[] = null;

    $this->fill($data);
  }

  /**
   * get the marital status in HPrim sante
   *
   * @param String $marital_status mediboard marital status
   *
   * @return string
   */
  function getMaritalStatus($marital_status) {
    switch ($marital_status) {
      case "S":
      case "M":
      case "D":
      case "A":
      case "W":
        $result = $marital_status;
        break;
      default:
        $result = "U";
    }
    return $result;
  }
}
