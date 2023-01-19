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
 * abstDomain: V10429 (C-0-D11555-V13940-V10429-cpt)
 */
class CCDARoleClassPartitive extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'CONT',
    'MBR',
    'PART',
  );
  public $_union = array (
    'RoleClassIngredientEntity',
    'RoleClassLocatedEntity',
    'RoleClassSpecimen',
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