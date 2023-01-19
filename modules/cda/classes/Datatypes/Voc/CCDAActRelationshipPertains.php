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
 * specDomain: V10329 (C-0-D10317-V10329-cpt)
 */
class CCDAActRelationshipPertains extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'PERT',
    'AUTH',
    'CAUS',
    'COVBY',
    'DRIV',
    'EXPL',
    'ITEMSLOC',
    'LIMIT',
    'MFST',
    'NAME',
    'PREV',
    'REFR',
    'REFV',
    'SUBJ',
    'SUMM',
  );
  public $_union = array (
    'ActRelationshipAccounting',
    'TemporallyPertains',
    'hasSupport',
  );


  /**
   * Retourne les propri�t�s
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}