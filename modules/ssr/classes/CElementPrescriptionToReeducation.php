<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Prescription\CElementPrescription;

/**
 * Classe abstraite d'association entre éléments de prescription et codes des nomenclatures SSR
 */
class CElementPrescriptionToReeducation extends CMbObject {
  // DB Fields
  public $element_prescription_id;
  public $code;
  public $commentaire;
  public $quantite;

  public $_ref_element_prescription;

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                            = parent::getProps();
    $props["element_prescription_id"] = "ref notNull class|CElementPrescription";
    $props["code"]                    = "str notNull length|7";
    $props["commentaire"]             = "str";
    $props["quantite"]                = "float default|1";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = CAppUI::tr("CElementPrescriptionToReeducation-code") . " $this->code";
  }

  /**
   * Charge l'élément de prescription associé
   *
   * @return CElementPrescription
   */
  function loadRefElementPrescription() {
    return $this->_ref_element_prescription = $this->loadFwdRef("element_prescription_id", true);
  }
}
