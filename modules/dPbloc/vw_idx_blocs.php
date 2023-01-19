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

$bloc_id  = CView::get("bloc_id", "ref class|CBlocOperatoire");
$salle_id = CView::get("salle_id", "ref class|CSalle");
$sspi_id  = CView::get("sspi_id", "ref class|CSSPI");

CView::checkin();

$poste = new CPosteSSPI();
$postes_no_sspi = $poste->countList(array("sspi_id" => "IS NULL"));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("bloc"          , $bloc_id);
$smarty->assign("salle"         , $salle_id);
$smarty->assign("sspi"          , $sspi_id);
$smarty->assign("postes_no_sspi", $postes_no_sspi);

$smarty->display("vw_idx_blocs");
