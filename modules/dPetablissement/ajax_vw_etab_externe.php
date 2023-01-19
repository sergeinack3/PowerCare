<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CEtabExterne;

CCanDo::checkRead();
$page     = CView::get("page", "num default|0");
$nom      = trim(CView::get("nom", "str", true));
$cp       = trim(CView::get("cp", "numchar", true));
$ville    = trim(CView::get("ville", "str", true));
$finess   = trim(CView::get("finess", "numchar", true));
$selected = CView::get("selected", "bool default|0");
CView::checkin();

$etab_externe = new CEtabExterne();

$where  = array();
$fields = array("nom", "cp", "ville", "finess");

foreach ($fields as $_field) {
  if ($$_field) {
    $where[$_field] = "LIKE '%{$$_field}%'";
  }
}

$step = 40;

$etab_externes = $etab_externe->loadList($where, "nom, cp, ville", "$page, $step");
$total         = $etab_externe->countList($where);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page"         , $page);
$smarty->assign("step"         , $step);
$smarty->assign("total"        , $total);
$smarty->assign("etab_externes", $etab_externes);
$smarty->assign("selected"     , $selected);
$smarty->display("inc_vw_etab_externe.tpl");
