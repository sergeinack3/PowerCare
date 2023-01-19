<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Utilisation d'un modèle
 */
CCanDo::checkRead();

$compte_rendu_id = CValue::get("compte_rendu_id");

$compte_rendu = new CCompteRendu;
$compte_rendu->load($compte_rendu_id);

$modeles = array();

switch ($compte_rendu->type) {
  case "header":
    $modeles = $compte_rendu->loadBackRefs("modeles_headed", "nom", null, null, null, null, null, array("object_id" => "IS NULL"));
    break;
  case "preface":
    $modeles = $compte_rendu->loadBackRefs("modeles_prefaced", "nom");
    break;
  case "body":
    $links = $compte_rendu->loadBackRefs("pack_links");
    $modeles = CMbObject::massLoadFwdRef($links, "pack_id");
    break;
  case "ending":
    $modeles = $compte_rendu->loadBackRefs("modeles_ended", "nom");
    break;
  case "footer":
    $modeles = $compte_rendu->loadBackRefs("modeles_footed", "nom", null, null, null, null, null, array("object_id" => "IS NULL"));
    break;
}

$counts = array();
$users  = array();
if ($compte_rendu->type == "body") {
  $ds = $compte_rendu->getDS();
  $query = "SELECT `author_id`, COUNT(*) AS `total`
    FROM `compte_rendu`
    WHERE `modele_id` = '$compte_rendu->_id'
    GROUP BY `author_id`
    ORDER BY `total` DESC
  ";
  $counts = $ds->loadHashList($query);

  $user = CMediusers::get();
  $users = $user->loadAll(array_keys($counts));
  CMbObject::massLoadFwdRef($users, "function_id");

  /** @var $_user CMediusers */
  foreach ($users as $_user) {
    $_user->loadRefFunction();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("modeles"     , $modeles);
$smarty->assign("counts"      , $counts);
$smarty->assign("users"       , $users);
$smarty->assign("compte_rendu", $compte_rendu);

$smarty->display("inc_vw_utilisation");
