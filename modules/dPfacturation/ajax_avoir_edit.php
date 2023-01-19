<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureAvoir;

CCanDo::checkEdit();
$facture_class  = CView::get("facture_class", "enum list|CFactureCabinet|CFactureEtablissement");
$facture_id     = CView::get("facture_id", "ref class|$facture_class");
$avoir_id       = CView::get("facture_avoir_id", "str");
CView::checkin();

$avoir = new CFactureAvoir();
if ($avoir_id) {
  $avoir->load($avoir_id);
}
else {
  $avoir->object_class = $facture_class;
  $avoir->object_id    = $facture_id;
}

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("avoir",  $avoir);

$smarty->display("inc_avoir_edit");
