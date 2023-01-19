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
 * abstDomain: V10685 (C-0-D10684-V10685-cpt)
 */
class CCDACalendarCycleTwoLetter extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'CD',
    'CH',
    'CM',
    'CN',
    'CS',
    'CW',
    'CY',
    'DM',
    'DW',
    'DY',
    'HD',
    'MY',
    'NH',
    'SN',
    'WY',
  );
  public $_union = array (
    'GregorianCalendarCycle',
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