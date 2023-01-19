<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CAnesthPeropChapitre;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

CCanDo::checkRead();
$keywords       = CView::get("keywords", "str");
$context        = CView::get("context", "enum list|chapitre|categorie|geste|precision default|geste");
$see_all_gestes = CView::get("see_all_gestes", "bool default|0", true);
CView::checkin();

$where          = array();
$where["actif"] = " = '1'";

if ($keywords) {
  $where["libelle"] = " LIKE '%$keywords%'";
}

switch ($context) {
  case "chapitre":
    $object  = new CAnesthPeropChapitre();
    $objects = $object->loadGroupList($where, "libelle ASC");
    break;
  case "categorie":
    $object  = new CAnesthPeropCategorie();
    $objects = $object->loadGroupList($where, "libelle ASC");

    CStoredObject::massLoadFwdRef($objects, "chapitre_id");
    break;
  case "precision":
    $object  = new CGestePeropPrecision();
    $objects = $object->loadGroupList($where, "libelle ASC");

    $gestes     = CStoredObject::massLoadFwdRef($objects, "geste_perop_id");
    $categories = CStoredObject::massLoadFwdRef($gestes, "categorie_id");
    CStoredObject::massLoadFwdRef($categories, "chapitre_id");

    break;
  default:
    $group = CGroups::loadCurrent();
    $user  = CMediusers::get();

    if ($see_all_gestes) {
      $users     = $user->loadUsers();
      $functions = $group->loadFunctions();

      $where[] = "user_id " .CSQLDataSource::prepareIn(array_keys($users)). " OR function_id " .CSQLDataSource::prepareIn(array_keys($functions)). " OR group_id = '$group->_id'";
    }
    else {
      $function = $user->loadRefFunction();

      $where[] = "user_id = '$user->_id' OR function_id = '$function->_id' OR group_id = '$group->_id'";
    }

    $object  = new CGestePerop();
    $objects = $object->loadList($where, "libelle ASC");

    $categories = CStoredObject::massLoadFwdRef($objects, "categorie_id");
    CStoredObject::massLoadFwdRef($categories, "chapitre_id");

    break;
}

$results = array(
  "chapitres"  => array(),
  "categories" => array(),
  "gestes"     => array(),
  "precisions" => array()
);

foreach ($objects as $_object) {
  if ($_object instanceof CAnesthPeropCategorie) {
    $chapitre = $_object->loadRefChapitre();

    $results["chapitres"][$chapitre->_id ?: 0] = $chapitre->_id ?: "0";
    $results["categories"][$_object->_id] = $_object->_id;
  }
  elseif ($_object instanceof CGestePerop) {
    $categorie = $_object->loadRefCategory();
    $chapitre  = $categorie->loadRefChapitre();

    $results["chapitres"][$chapitre->_id ?: 0]   = $chapitre->_id ?: "0";
    $results["categories"][$categorie->_id ?: 0] = $categorie->_id ?: "0";
    $results["gestes"][$_object->_id]       = $_object->_id;
  }
  elseif ($_object instanceof CGestePeropPrecision) {
    $geste     = $_object->loadRefGestePerop();
    $categorie = $geste->loadRefCategory();
    $chapitre  = $categorie->loadRefChapitre();

    $results["chapitres"][$chapitre->_id ?: 0]   = $chapitre->_id ?: "0";
    $results["categories"][$categorie->_id ?: 0] = $categorie->_id ?: "0";
    $results["gestes"][$geste->_id]         = $geste->_id;
    $results["precisions"][$_object->_id]   = $_object->_id;
  }
  else {
    $results["chapitres"][$_object->_id] = $_object->_id;
  }
}

CApp::json($results);
