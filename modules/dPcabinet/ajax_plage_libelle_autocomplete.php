<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;

CCanDo::check();

$keywords    = CView::get("plage_libelle", "str");
$function_id = CView::get("function_id", 'ref class|CFunctions');
$date        = CView::get("date", 'date default|now');
$prat_id     = CView::get("prat_id", 'ref class|CMediusers');
$rdv         = CView::get("rdv", 'bool default|0');

CView::checkin();
CView::enableSlave();

$keywords = CMbString::removeDiacritics($keywords, CMbString::LOWERCASE);

$listPrat = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);

$where_plage = array();

if ($rdv) {
  $where_plage["chir_id"] = " = '$prat_id'";
}
else {
  $where_plage["chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));
}

if ($keywords) {
  $where_plage["libelle"] = " LIKE '%$keywords%'";
}

$where_plage["date"] = " >= '$date'";

$order    = "libelle ASC";
$group_by = "libelle";

$plage_consult = new CPlageconsult();
$plages        = $plage_consult->loadList($where_plage, $order, null, $group_by);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("plages", $plages);
$smarty->display("inc_plage_libelle_autocomplete.tpl");
