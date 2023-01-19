<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;

/**
 * Tâche automatique de suppression de documents déclarés temporaires
 */
CCanDo::checkEdit();

$modele = new CCompteRendu();
$limit = CValue::get("limit", 100);

$where = array();
$where["object_id"] = "IS NULL";
$where["purgeable"] = "= '1'";

$modeles = $modele->loadList($where);

CMbObject::massCountBackRefs($modeles, "documents_generated");

foreach ($modeles as $_modele) {
  $documents = $_modele->loadBackRefs("documents_generated", null, $limit);
  foreach ($documents as $_doc) {
    $_doc->delete();
  }
}
