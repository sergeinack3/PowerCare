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
 * vocSet: D10196 (C-0-D10196-cpt)
 */
class CCDAActMood extends CCDA_Datatype_Voc {

  public $_enumeration = array (
  );
  public $_union = array (
    'ActMoodCompletionTrack',
    'ActMoodPredicate',
    'x_ActMoodDefEvn',
    'x_ActMoodDefEvnRqoPrmsPrp',
    'x_ActMoodDocumentObservation',
    'x_ActMoodEvnOrdPrmsPrp',
    'x_ActMoodIntentEvent',
    'x_ActMoodOrdPrms',
    'x_ActMoodOrdPrmsEvn',
    'x_ActMoodRqoPrpAptArq',
    'x_DocumentActMood',
    'x_DocumentEncounterMood',
    'x_DocumentProcedureMood',
    'x_DocumentSubstanceMood',
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