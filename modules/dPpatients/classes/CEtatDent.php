<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;

/**
 * The CEtatDent Class
 */
class CEtatDent extends CMbObject {
  static $_dents = array(
    //'10' => array(127, 112, 30),
    '11' => array(116, 33, 11),
    '12' => array(97, 44, 11),
    '13' => array(79, 55, 12),
    '14' => array(70, 74, 12),
    '15' => array(61, 93, 13),
    '16' => array(55, 118, 17),
    '17' => array(51, 146, 16),
    '18' => array(50, 174, 15),
    '21' => array(137, 33, 11),
    '22' => array(156, 44, 11),
    '23' => array(174, 55, 12),
    '24' => array(183, 74, 12),
    '25' => array(192, 94, 13),
    '26' => array(198, 118, 17),
    '27' => array(201, 146, 16),
    '28' => array(203, 174, 15),
    //'30' => array(127, 272, 30),
    '31' => array(135, 356, 9),
    '32' => array(150, 349, 9),
    '33' => array(164, 338, 11),
    '34' => array(177, 322, 11),
    '35' => array(186, 303, 12),
    '36' => array(195, 279, 18),
    '37' => array(199, 250, 16),
    '38' => array(203, 222, 15),
    '41' => array(118, 356, 9),
    '42' => array(103, 348, 9),
    '43' => array(89, 338, 11),
    '44' => array(76, 323, 11),
    '45' => array(66, 304, 12),
    '46' => array(58, 279, 18),
    '47' => array(54, 250, 16),
    '48' => array(49, 223, 15),
    //'50' => array(324, 162, 19),
    '51' => array(318, 114, 7),
    '52' => array(307, 120, 8),
    '53' => array(298, 131, 9),
    '54' => array(290, 147, 11),
    '55' => array(285, 166, 12),
    '61' => array(331, 114, 7),
    '62' => array(342, 120, 8),
    '63' => array(351, 131, 9),
    '64' => array(357, 147, 11),
    '65' => array(363, 166, 12),
    //'70' => array(324, 231, 19),
    '71' => array(330, 271, 6),
    '72' => array(339, 265, 7),
    '73' => array(350, 255, 8),
    '74' => array(357, 243, 8),
    '75' => array(365, 227, 10),
    '81' => array(319, 271, 6),
    '82' => array(309, 265, 7),
    '83' => array(298, 255, 8),
    '84' => array(291, 242, 8),
    '85' => array(282, 228, 10),
  );

  public $etat_dent_id;

  // DB Fields
  public $dossier_medical_id;
  public $dent;
  public $etat;

  /** @var CDossierMedical */
  public $_ref_dossier_medical;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'etat_dent';
    $spec->key   = 'etat_dent_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["dossier_medical_id"] = "ref notNull class|CDossierMedical back|etats_dent";
    $props["dent"]               = "num notNull pos";
    $props["etat"]               = "enum list|bridge|pivot|mobile|appareil|implant|defaut|absence|app-partiel";

    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->updatePlainFields();

      $etat_dent                     = new CEtatDent();
      $etat_dent->dent               = $this->dent;
      $etat_dent->dossier_medical_id = $this->dossier_medical_id;

      if ($etat_dent->loadMatchingObject()) {
        $this->_id = $etat_dent->_id;
      }
    }

    return parent::store();
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    return $this->loadRefDossierMedical();
  }

  /**
   * Charge le dossier médical
   *
   * @return CDossierMedical
   * @throws \Exception
   */
  function loadRefDossierMedical() {
    return $this->_ref_dossier_medical = $this->loadFwdRef("dossier_medical_id");
  }
}
