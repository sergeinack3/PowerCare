<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Voc;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Voc;

/**
 * specDomain: V10430 (C-0-D11555-V13940-V10429-V10430-cpt)
 */
class CCDARoleClassIngredientEntity extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'INGR',
    'ACTI',
    'ACTM',
    'ADTV',
    'BASE',
  );
  public $_union = array (
    'RoleClassInactiveIngredient',
  );


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}