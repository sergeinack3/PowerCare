<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Classe CAppelSejour
 *
 * Appels de séjour
 */
class CAppelSejour extends CMbObject {
  // DB Table key
  public $appel_id;

  // DB Table key
  public $sejour_id;
  public $user_id;
  public $datetime;
  public $type;
  public $etat;
  public $commentaire;

  public $_open_form = 0;

  //Distant field
  public $_ref_user;
  /** @var CSejour */
  public $_ref_sejour;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'sejour_appel';
    $spec->key   = 'appel_id';

    $spec->events = array(
      'appel'     => array(
        'reference1' => array('CSejour', 'sejour_id'),
        'reference2' => array('CPatient', 'sejour_id.patient_id'),
      ),
      'appel_j_moins_1_auto' => array(
        'auto' => true,
        'reference1' => array('CSejour', 'sejour_id'),
        'reference2' => array('CPatient', 'sejour_id.patient_id'),
      ),
      'appel_j_plus_1_auto' => array(
        'auto' => true,
        'reference1' => array('CSejour', 'sejour_id'),
        'reference2' => array('CPatient', 'sejour_id.patient_id'),
      ),
    );

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["sejour_id"]   = "ref notNull class|CSejour back|appels";
    $props["user_id"]     = "ref notNull class|CMediusers back|appels_user";
    $props["datetime"]    = "dateTime notNull";
    $props["type"]        = "enum notNull list|admission|sortie default|admission";
    $props["etat"]        = "enum notNull list|realise|echec default|realise";
    $props["commentaire"] = "text helped";

    return $props;
  }

  /**
   * @inheritDoc
   */
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    // Lancement des formulaires automatiques lors de l'appel J-1 et J+1
    if ($this->_id && $this->_open_form && CModule::getActive("forms")) {
      $event_name = $this->type == 'admission' ? "appel_j_moins_1_auto" : "appel_j_plus_1_auto";

      CAppUI::callbackAjax("Appel.afterTriggerFormsAppel", $this->_guid, $event_name);
    }

    return null;
  }

  /**
   * Load the user
   *
   * @return CMediusers The user object
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id");
  }

  /**
   * Load sejour
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }
}
