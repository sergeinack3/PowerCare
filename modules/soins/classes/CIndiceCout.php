<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbObject;

class CIndiceCout extends CMbObject {
  public $indice_cout_id;

  // DB Fields
  public $nb;
  public $ressource_soin_id;
  public $element_prescription_id;

  /** @var CRessourceSoin */
  public $_ref_ressource_soin;

  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'indice_cout';
    $spec->key   = 'indice_cout_id';

    return $spec;
  }

  function getProps() {
    $props                            = parent::getProps();
    $props["nb"]                      = "num notNull";
    $props["ressource_soin_id"]       = "ref class|CRessourceSoin notNull back|indices_couts";
    $props["element_prescription_id"] = "ref class|CElementPrescription notNull back|indices_cout";

    return $props;
  }

  /**
   * @return CRessourceSoin
   */
  function loadRefRessourceSoin() {
    return $this->_ref_ressource_soin = $this->loadFwdRef("ressource_soin_id", true);
  }
}
