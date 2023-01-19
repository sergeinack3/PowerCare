<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CDossierMedical;


class CDoCopyAntecedent extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CAntecedent");
  }

  /**
   * @inheritdoc
   */
  function doBind() {
    parent::doBind();

    // recuperation du sejour_id
    $_sejour_id = CValue::post("_sejour_id", null);

    // si pas de sejour_id, redirection
    if (!$_sejour_id) {
      $this->doRedirect();
    }

    // Creation du nouvel antecedent
    unset($_POST["antecedent_id"]);
    $this->_obj                = $this->_old;
    $this->_obj->_id           = null;
    $this->_obj->antecedent_id = null;

    // Calcul de la valeur de l'id du dossier_medical du sejour
    $this->_obj->dossier_medical_id = CDossierMedical::dossierMedicalId($_sejour_id, "CSejour");
  }
}

$do = new CDoCopyAntecedent();
$do->doIt();
