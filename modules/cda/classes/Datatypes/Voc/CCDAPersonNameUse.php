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
 * abstDomain: V200 (C-0-D15913-V200-cpt)
 */
class CCDAPersonNameUse extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'A',
    'ASGN',
    'C',
    'I',
    'L',
    'R',
  );
  public $_union = array (
    'EntityNameSearchUse',
    'NamePseudonymUse',
    'NameRepresentationUse',
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