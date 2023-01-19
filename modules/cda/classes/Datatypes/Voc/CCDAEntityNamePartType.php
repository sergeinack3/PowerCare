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
 * vocSet: D15880 (C-0-D15880-cpt)
 */
class CCDAEntityNamePartType extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'DEL',
    'FAM',
    'GIV',
    'PFX',
    'SFX',
  );
  public $_union = array (
    'x_OrganizationNamePartType',
    'x_PersonNamePartType',
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