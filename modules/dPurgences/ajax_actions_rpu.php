<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\CRPUCategorie;
use Ox\Mediboard\Urgences\CRPULinkCat;

CCanDo::checkEdit();

$rpu_id = CView::get("rpu_id", "ref class|CRPU");

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefCategories();
$rpu->loadRefsAttentes();
$rpu->loadRefSejour();

$categorie_rpu  = new CRPUCategorie();
$categories_rpu = $categorie_rpu->loadGroupList(array("actif" => "= '1'"));

foreach ($categories_rpu as $_categorie_rpu) {
  $_categorie_rpu->loadRefIcone();
}

$link_cat         = new CRPULinkCat();
$link_cat->rpu_id = $rpu->_id;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rpu", $rpu);
$smarty->assign("categories_rpu", $categories_rpu);
$smarty->assign("link_cat", $link_cat);

$smarty->display("inc_actions_rpu");
