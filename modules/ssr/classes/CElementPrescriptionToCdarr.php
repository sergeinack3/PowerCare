<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;

/**
 * Classe d'association entre éléments de prescription et codes CdARR
 */
class CElementPrescriptionToCdarr extends CElementPrescriptionToReeducation {
  // DB Table key
  public $element_prescription_to_cdarr_id;

  public $_ref_activite_cdarr;
  public $_count_cdarr_by_type;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'element_prescription_to_cdarr';
    $spec->key   = 'element_prescription_to_cdarr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props         = parent::getProps();
    $props["element_prescription_id"] .= " back|cdarrs";
    $props["code"] = "str notNull length|4";

    return $props;
  }

  /**
   * @see parent::check()
   */
  function check() {
    // Verification du code Cdarr saisi
    $code_cdarr = CActiviteCdARR::get($this->code);
    if (!$code_cdarr->code) {
      return CAppUI::tr("CActiviteCdARR.code_invalide");
    }

    return parent::check();
  }

  /**
   * Charge l'activité CdARR associée
   *
   * @return CActiviteCdARR
   */
  function loadRefActiviteCdarr() {
    $activite = CActiviteCdARR::get($this->code);
    $activite->loadRefTypeActivite();

    return $this->_ref_activite_cdarr = $activite;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefActiviteCdarr();
  }
}
