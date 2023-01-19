<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Voc;

use Ox\Core\CMbSecurity;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Voc;

/**
 * specDomain: V13948 (C-0-D11527-V13856-V19445-V19442-V18938-V13948-cpt)
 */
class CCDAActClinicalDocument extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'DOCCLIN',
    'CDALVLONE',
  );
  public $_union = array (
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

  /**
   * Créer un UUID
   *
   * @return string
   */
  static function generateUUID() {
    return mb_strtoupper(CMbSecurity::generateUUID());
  }
}
