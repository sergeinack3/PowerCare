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
 * abstDomain: V19447 (C-0-D10317-V19447-cpt)
 */
class CCDAx_ActRelationshipEntryRelationship extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'XCRPT',
    'COMP',
    'RSON',
    'SPRT',
    'CAUS',
    'GEVL',
    'MFST',
    'REFR',
    'SAS',
    'SUBJ',
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
}