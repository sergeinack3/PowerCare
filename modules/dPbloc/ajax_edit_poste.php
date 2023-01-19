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
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$poste_id  = CView::get("poste_id", "ref class|CPosteSSPI");
$sspi_id   = CView::get("sspi_id", "ref class|CSSPI");
$show_sspi = CView::get("show_sspi", "bool");

CView::checkin();

$poste = new CPosteSSPI();

if (!$poste->load($poste_id)) {
  $poste->sspi_id = $sspi_id;
}

$sspis = array();
if ($show_sspi) {
  $group = CGroups::loadCurrent();
  $sspis = $group->loadSSPIs();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("poste"    , $poste);
$smarty->assign("show_sspi", $show_sspi);
$smarty->assign("sspis"    , $sspis);

$smarty->display("inc_edit_poste");