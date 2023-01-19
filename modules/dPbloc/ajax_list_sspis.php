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
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$sspi_id = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

$curr_group = CGroups::loadCurrent();

$sspis_list = $curr_group->loadSSPIs(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sspis_list", $sspis_list);
$smarty->assign("sspi_id"   , $sspi_id);

$smarty->display("inc_list_sspis");