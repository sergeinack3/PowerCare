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
 * vocSet: D10317 (C-0-D10317-cpt)
 */
class CCDAActRelationshipType extends CCDA_Datatype_Voc {

  public $_enumeration = array (
  );
  public $_union = array (
    'ActRelationshipConditional',
    'ActRelationshipHasComponent',
    'ActRelationshipOutcome',
    'ActRelationshipPertains',
    'ActRelationshipSequel',
    'x_ActRelationshipDocument',
    'x_ActRelationshipEntry',
    'x_ActRelationshipEntryRelationship',
    'x_ActRelationshipExternalReference',
    'x_ActRelationshipPatientTransport',
    'x_ActRelationshipPertinentInfo',
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