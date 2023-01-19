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

CCanDo::checkEdit();

$salles_ids      = CView::get("salles_ids", "str");
$date            = CView::get("date", "date");
$chir_id         = CView::get("chir_id", "num");
$distinct_plages = CView::get("distinct_plages", "bool default|0", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("salles_ids"     , $salles_ids);
$smarty->assign("date"           , $date);
$smarty->assign("chir_id"        , $chir_id);
$smarty->assign("distinct_plages", $distinct_plages);

$smarty->display("vw_multi_salle.tpl");