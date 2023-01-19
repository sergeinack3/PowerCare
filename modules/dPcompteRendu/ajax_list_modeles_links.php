<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CModeleToPack;
use Ox\Mediboard\CompteRendu\CPack;

/**
 * Liste des modèles d'un pack de modèles
 */

CCanDo::checkEdit();

$pack_id = CView::get("pack_id", "ref class|CPack");

CView::checkin();

// Chargement du pack
$pack = new CPack();
$pack->load($pack_id);
$pack->loadBackRefs("modele_links", "modele_to_pack_id");

$modele_to_pack = new CModeleToPack();
$where["pack_id"] = "= '$pack_id'";
$modeles_to_pack = $modele_to_pack->loadList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pack", $pack);
$smarty->assign("modeles_to_pack", $modeles_to_pack);

$smarty->display("inc_list_modeles_links");
