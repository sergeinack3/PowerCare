<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkEdit();

$object_class  = CView::get(
  "object_class", "enum list|CPrestationPonctuelle|CPrestationJournaliere default|CPrestationPonctuelle", true
);
$prestation_id = CView::get("prestation_id", "ref class|$object_class", true);

CView::checkin();

$smarty = new CSmartyDP();

$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("object_class", $object_class);

$smarty->display("vw_prestations.tpl");

