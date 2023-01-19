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
 * abstDomain: V19604 (C-0-D11527-V13856-V19604-cpt)
 */
class CCDAx_ActClassDocumentEntryAct extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'ACT',
    'ACCM',
    'CONS',
    'CTTEVENT',
    'INC',
    'INFRM',
    'PCPR',
    'REG',
    'SPCTRT',
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