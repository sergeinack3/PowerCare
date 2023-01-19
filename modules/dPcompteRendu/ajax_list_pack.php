<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Liste des packs de modèles pour un utilisateur
 */
CCanDo::checkRead();

$user_id      = CView::get("user_id", "ref class|CMediusers", true);
$function_id  = CView::get("function_id", "ref class|CFunctions", true);
$object_class = CView::get("object_class", "str", true);

CView::checkin();

$user     = CMediusers::get($user_id);
$owner    = "prat";
$owner_id = $user->_id;
$owners   = $user->getOwners();

if ($function_id) {
  $function = new CFunctions();
  $function->load($function_id);

  $owner    = "func";
  $owner_id = $function->_id;
  $owners   = array(
    "func"     => $function,
    "etab"     => $function->loadRefGroup(),
    "instance" => CCompteRendu::getInstanceObject()
  );
}

$packs = CPack::loadAllPacksFor($owner_id, $owner, $object_class);
if ($function_id) {
  unset($packs["prat"]);
}

foreach ($packs as $_packs_by_owner) {
  CStoredObject::massLoadBackRefs($_packs_by_owner, "modele_links");
  foreach ($_packs_by_owner as $_pack) {
    /** @var $_pack CPack */
    $_pack->loadRefOwner();
    $_pack->loadBackRefs("modele_links");
    $_pack->loadHeaderFooter();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("owners", $owners);
$smarty->assign("packs", $packs);

$smarty->display("inc_list_pack");
