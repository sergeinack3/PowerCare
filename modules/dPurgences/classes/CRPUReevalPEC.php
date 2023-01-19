<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * The CRPUReevalPEC class
 * Reevaluate PEC nurse
 */
class CRPUReevalPEC extends CMbObject {
  // DB Table key
  public $rpu_reeval_pec_id;

  // DB Fields
  public $rpu_id;
  public $user_id;
  public $ccmu;
  public $cimu;
  public $french_triage;
  public $datetime;
  public $commentaire;

  public $_color_cimu_reeval_pec;

  /** @var CRPU */
  public $_ref_rpu;
  /** @var CMediusers */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'rpu_reeval_pec';
    $spec->key   = 'rpu_reeval_pec_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $impose_degre_urgence = CAppUI::gconf("dPurgences CRPU impose_degre_urgence") == 1;

    $props                  = parent::getProps();
    $props["rpu_id"]        = "ref notNull class|CRPU back|reevaluations_pec_rpu";
    $props["user_id"]       = "ref class|CMediusers back|rpu_reevals";
    $props["datetime"]      = "dateTime notNull";
    $props["ccmu"]          = "enum " . ($impose_degre_urgence ? 'notNull ' : '') . "list|1|P|2|3|4|5|D";
    $props["cimu"]          = "enum list|5|4|3|2|1";
    $props["french_triage"] = "enum list|1|2|3A|3B|4|5";
    $props["commentaire"]   = "text helped";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
  }

  /**
   * Load the RPU
   *
   * @return CRPU
   * @throws Exception
   */
  function loadRefRPU() {
    return $this->_ref_rpu = $this->loadFwdRef("rpu_id", true);
  }

  /**
   * Load the user
   *
   * @return CMediusers
   * @throws Exception
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Return the CIMU color
   *
   * @return string
   */
  function getColorCIMUReevalPec() {
    return $this->_color_cimu_reeval_pec = "#" . CAppUI::gconf("dPurgences Display color_cimu_" . $this->cimu);
  }
}
