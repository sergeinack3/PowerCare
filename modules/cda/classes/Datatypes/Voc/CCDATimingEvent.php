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
 * vocSet: D10706 (C-0-D10706-cpt)
 */
class CCDATimingEvent extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'AC',
    'ACD',
    'ACM',
    'ACV',
    'HS',
    'IC',
    'ICD',
    'ICM',
    'ICV',
    'PC',
    'PCD',
    'PCM',
    'PCV',
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