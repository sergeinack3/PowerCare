<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$mode    = CView::get("mode", 'enum list|CMediusers|CFunctions|CGroups');
$prat_id = CView::getRefCheckRead("prat_id", 'ref class|CMediusers');

CView::checkin();

$prat = new CMediusers();
$prat->load($prat_id);

$prat->canDo()->checkEdit();

$prat->loadRefFunction()->loadRefGroup();

$tarif  = new CTarif();
/** @var CTarif[] $tarifs */
$tarifs = array();

if ($prat->_id) {
  $order = "description";
  switch ($mode) {
    case "CMediusers":
      $where = array();
      $where["chir_id"] = "= '$prat->_id'";
      $tarifs = $tarif->loadList($where, $order);
      break;
    case "CFunctions":
      $where = array();
      $where["function_id"] = "= '$prat->function_id'";
      $tarifs = $tarif->loadList($where, $order);
      break;
    case "CGroups":
      $where = array();
      $where["group_id"] = "= '".$prat->_ref_function->group_id."'";
      $tarifs = $tarif->loadList($where, $order);
      break;
    default:
  }
  foreach ($tarifs as $_tarif) {
    $_tarif->getPrecodeReady();
    $_tarif->getSecteur1Uptodate();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("mode"  , $mode);
$smarty->assign("prat"  , $prat);
$smarty->assign("tarif" , $tarif);
$smarty->assign("tarifs", $tarifs);

$smarty->display("inc_list_tarifs_by_owner.tpl");
