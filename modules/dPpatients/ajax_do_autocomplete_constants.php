<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::check();

$needle         = CView::request('_search_constants', 'str');
$show_main_unit = CView::request('show_main_unit', 'bool default|0');
/** @var bool $show_formfields If true, the formfields of the constants that have multiple formfields (like the ta), will be shown */
$show_formfields = CView::request('show_formfields', 'bool default|0');

CView::checkin();
CView::enableSlave();

$needle          = CMbString::removeDiacritics(strtolower($needle));
$list_constantes = CConstantesMedicales::$list_constantes;

$results = array();
foreach ($list_constantes as $_constant => $params) {
  if (strpos($_constant, 'cumul') !== false || $_constant[0] === '_') {
    continue;
  }

  if ($needle) {
    $search_elements   = array();
    $search_elements[] = CMbString::removeDiacritics(strtolower($_constant));
    $search_elements[] = CMbString::removeDiacritics(strtolower(CAppUI::tr("CConstantesMedicales-$_constant")));
    $search_elements[] = CMbString::removeDiacritics(strtolower(CAppUI::tr("CConstantesMedicales-$_constant-court")));
    $search_elements[] = CMbString::removeDiacritics(strtolower(CAppUI::tr("CConstantesMedicales-$_constant-desc")));
    if (strpos(implode('|', $search_elements), $needle) !== false) {
      $results[$_constant] = $params;
    }
  }
  else {
    $results[$_constant] = $params;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('results', $results);
$smarty->assign('show_main_unit', $show_main_unit);
$smarty->assign('show_formfields', $show_formfields);
$smarty->display('inc_autocomplete_constants.tpl');