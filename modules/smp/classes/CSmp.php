<?php
/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Smp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Class CSmp
 */
class CSmp extends CMbObject {
  /**
   * Construit le tag d'une venue en fonction des variables de configuration
   *
   * @param string $group_id Permet de charger l'id externe d'une venue pour un établissement donné si non null
   *
   * @return string
   */
  static function getTagVisitNumber($group_id = null) {
    // Pas de tag venue
    if (null == $tag_visit_number = CAppUI::conf("smp tag_visit_number")) {
      return;
    }

    // Permettre des id externes en fonction de l'établissement
    $group = CGroups::loadCurrent();
    if (!$group_id) {
      $group_id = $group->_id;
    }
    
    return str_replace('$g', $group_id, $tag_visit_number);
  }

  /**
   * @see parent::getDynamicTag
   */
  function getDynamicTag() {
    return CAppUI::conf("smp tag_visit_number");
  }
} 