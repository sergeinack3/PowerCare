<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Used in header of a view to check for access to a medical object needed to be checked
 */
class CAccessMedicalData extends CMbObject {

  /**
   * Check access for a sejour
   *
   * @param string|CSejour $sejour Sejour
   * @param bool           $modal  Modale
   *
   * @return bool
   */
  static function checkForSejour($sejour, $modal = true) {
    if (is_string($sejour)) {
      /** @var CSejour $sejour */
      $sejour = CMbObject::loadFromGuid($sejour);
    }

    $canAccess = CBrisDeGlace::checkForSejour($sejour, $modal);

    CLogAccessMedicalData::logForObject($sejour);

    return $canAccess;
  }

  /**
   * Log access for an object
   *
   * @param string|CMbObject $object  Object
   * @param string           $context Context
   *
   * @return void
   */
  static function logAccess($object, $context = "") {
    // Si la vue est en slave, pas d'enregistrement
    if (CView::$slavestate) {
        return;
    }

    if (is_string($object)) {
      $object = CMbObject::loadFromGuid($object);
    }

    if (!$object || !$object->_id) {
      return;
    }

    if ($object instanceof CSejour && CAppUI::gconf("dPpatients sharing multi_group") !== "full") {
      if ($object->group_id != CGroups::loadCurrent()->_id) {
        CAppUI::accessDenied($object);
      }
    }

    CApp::registerShutdown(function() use ($object, $context) {
        CLogAccessMedicalData::logForObject($object, $context);
    });
  }
}
