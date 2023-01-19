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

CCanDo::checkAdmin();

$sspi_id  = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

// Récupération des postes preop
$poste_preop = new CPosteSSPI();

$where = array(
  "sspi_id" => "IS NULL"
);

$postes_preop = $poste_preop->loadList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("postes_preop", $postes_preop);

$smarty->display("vw_list_postes_preop");