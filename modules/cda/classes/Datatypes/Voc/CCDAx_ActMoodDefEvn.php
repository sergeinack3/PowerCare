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
 * abstDomain: V19375 (C-0-D10196-V19375-cpt)
 */
class CCDAx_ActMoodDefEvn extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'DEF',
    'EVN',
  );
  public $_union = array (
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