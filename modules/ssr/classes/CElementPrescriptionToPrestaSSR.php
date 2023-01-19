<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

/**
 * Classe d'association entre éléments de prescription et prestations SSR
 */
class CElementPrescriptionToPrestaSSR extends CElementPrescriptionToReeducation {
  // DB Table key
  public $element_prescription_to_presta_ssr_id;

  public $_ref_presta_ssr = array();

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'element_prescription_to_presta_ssr';
    $spec->key   = 'element_prescription_to_presta_ssr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props         = parent::getProps();
    $props["element_prescription_id"] .= " back|prestas_ssr";
    $props["code"] = "str notNull";

    return $props;
  }

  /**
   * Charge la prestation SSR associée
   *
   * @return array
   */
  function loadRefPrestationSSR() {
    $presta = CPrestaSSR::get($this->code);

    return $this->_ref_presta_ssr = $presta;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefPrestationSSR();
  }
}
