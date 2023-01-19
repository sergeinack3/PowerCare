<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocage;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkEdit();

$blocage_id = CView::get("blocage_id", 'ref class|CBlocage');
$salle_id   = CView::get('salle_id', 'ref class|CSalle');
$date       = CView::get('date', 'date default|now');

CView::checkin();

$blocage = new CBlocage;
$blocage->load($blocage_id);

if (!$blocage->_id) {
  if ($salle_id) {
    $blocage->salle_id = $salle_id;
  }
  $blocage->deb = $date . ' 00:00:00';
  $blocage->fin = $date . ' 23:59:00';
}

$bloc = new CBlocOperatoire();
$where             = array();
$where["group_id"] = " = '".CGroups::loadCurrent()->_id."'";
$where["actif"]    = " = '1'";
/** @var CBlocOperatoire[] $blocs */
$blocs = $bloc->loadListWithPerms(PERM_READ, $where, "nom");

foreach ($blocs as $_bloc) {
  $_bloc->loadRefsSalles(array("actif" => "= '1'"));
}

$smarty = new CSmartyDP;

$smarty->assign("blocage", $blocage);
$smarty->assign("blocs"  , $blocs);

$smarty->display("inc_edit_blocage.tpl");
