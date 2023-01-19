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
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$type = CView::get('type', 'enum list|prov|dest default|prov');

CView::checkin();

if ($type == 'dest') {
  $dests = array("0");
  $trads = array_merge($dests, CSejour::$destination_values);
}
else {
  $etab = new CEtabExterne();
  $trads = explode('|', $etab->_specs['provenance']->list);
}

$smarty = new CSmartyDP();
$smarty->assign('type', ($type == 'dest') ? 'destination' : 'provenance');
$smarty->assign('trads', $trads);
$smarty->display('vw_types_prov_dest');
