<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPosteSSPI;

CCanDo::checkRead();

$poste   = CView::get("_poste_preop_id_autocomplete", "str");
$sspi_id = CView::get("sspi_id", "ref class|CSSPI");
CView::checkin();

$poste_sspi = new CPosteSSPI();
$ds         = $poste_sspi->getDS();
$where      = [
  "actif" => "= '1'",
  "type"  => "= 'preop'",
];

if ($sspi_id) {
  $where[] = $ds->prepare("sspi_id = ? OR sspi_id IS NULL", $sspi_id);
}

$matches = $poste_sspi->getAutocompleteList($poste, $where);

$template = $poste_sspi->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', true);
$smarty->assign('field', '_view');
$smarty->assign('show_view', true);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);


$smarty->display("inc_field_autocomplete");
