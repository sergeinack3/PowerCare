<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CCSVImportTranslations;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::checkAdmin();

$start = CView::get('start', 'num default|0');
$step  = CView::get('step', 'num default|30');

CView::checkin();

if (!CAppUI::conf('debug')) {
  CAppUI::commonError(); // An error occured
}

$locales = CAppUI::getLocalesFromFiles();

$overwrite    = new CTranslationOverwrite();
$total        = $overwrite->countList();
$translations = $overwrite->loadList(null, $overwrite->_spec->key . ' ASC', "$start,$step");

$all_translations = array();
foreach ($translations as $_translation) {
  $old_value = isset($locales[$_translation->source]) ? $locales[$_translation->source] : '';

  $tmp = list(, $value, $prefix) = CAppUI::splitLocale($_translation->source);
  $module    = CCSVImportTranslations::getCorrespondingModule($prefix, $value);

  if (!array_key_exists($module, $all_translations)) {
    $all_translations[$module] = array();
  }

  $all_translations[$module][] = array(
    'id'        => $_translation->_id,
    'key'       => $_translation->source,
    'old_value' => $old_value,
    'value'     => $_translation->translation,
    'language'  => $_translation->language,
  );
}

$smarty = new CSmartyDP();
$smarty->assign('translations', $all_translations);
$smarty->assign('total', $total);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->display('vw_integrate_translations');