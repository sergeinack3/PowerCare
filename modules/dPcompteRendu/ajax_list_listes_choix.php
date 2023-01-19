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
use Ox\Mediboard\CompteRendu\CListeChoix;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Affichage d'une liste de listes de choix
 */

CCanDo::checkRead();

$liste_id    = CView::get("liste_id", "ref class|CListeChoix", true);
$user_id     = CView::get("user_id", "ref class|CMediusers", true);
$function_id = CView::get("function_id", "ref class|CFunctions", true);

CView::checkin();

if ($user_id) {
  $user   = CMediusers::get($user_id);
  $owners = $user->getOwners();
}
else {
  $function = new CFunctions();
  $function->load($function_id);
  $owners = $function->getOwners();
  $user_id = "";
}

$listes = CListeChoix::loadAllFor($user_id, $function_id);

// Modèles associés
foreach ($listes as $_listes) {
  CStoredObject::massLoadFwdRef($_listes, "compte_rendu_id");
  foreach ($_listes as $_liste) {
    /** @var $_liste CListeChoix */
    $_liste->loadRefModele();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("liste_id", $liste_id);
$smarty->assign("owners"  , $owners);
$smarty->assign("listes"  , $listes);

$smarty->display("inc_list_listes_choix");
