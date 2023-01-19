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
 * specDomain: V10337 (C-0-D10317-V10337-cpt)
 */
class CCDAActRelationshipSequel extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'SEQL',
    'APND',
    'DOC',
    'ELNK',
    'GEN',
    'GEVL',
    'INST',
    'MTCH',
    'OPTN',
    'REV',
    'UPDT',
    'XFRM',
  );
  public $_union = array (
    'ActRelationshipExcerpt',
    'ActRelationshipFulfills',
    'ActRelationshipReplacement',
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