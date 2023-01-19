<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$date_session = CAppUI::pref("suivisalleAutonome") ? false : true;
$date         = CView::get('date', 'date default|now', $date_session);
$blocs_ids    = CView::get('blocs_ids', 'str', true);
$display      = CView::get('display', 'enum list|normal|fullscreen default|normal');

CView::checkin();

/** @var CBlocOperatoire[] */
$blocs  = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, 'nom', array("actif" => "= '1'"));

$selected_blocs = array();
foreach ($blocs as $bloc) {
  $bloc->loadRefsSalles(array("actif" => "= '1'"));
  if (is_array($blocs_ids) && in_array($bloc->_id, $blocs_ids)) {
    $selected_blocs[$bloc->_id] = $bloc;
  }
}

$smarty = new CSmartyDP('modules/dPbloc');
$smarty->assign('date', $date);
$smarty->assign('blocs', $blocs);
$smarty->assign('selected_blocs', $selected_blocs);
$smarty->assign('display', $display);
$smarty->display('vw_horizontal_planning.tpl');