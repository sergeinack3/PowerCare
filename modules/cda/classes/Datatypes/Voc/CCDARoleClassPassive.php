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
 * abstDomain: V19105 (C-0-D11555-V13940-V19313-V19105-cpt)
 */
class CCDARoleClassPassive extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'ACCESS',
    'BIRTHPL',
    'EXPR',
    'HLD',
    'HLTHCHRT',
    'IDENT',
    'MNT',
    'OWN',
    'RGPR',
    'TERR',
    'WRTE',
  );
  public $_union = array (
    'RoleClassDistributedMaterial',
    'RoleClassManufacturedProduct',
    'RoleClassServiceDeliveryLocation',
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