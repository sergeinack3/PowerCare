<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::checkEdit();

$start    = CView::get('start', 'num default|0');
$step     = CView::get('step', 'num default|50');
$source   = CView::get('source', 'str');
$language = CView::get('language', 'str');

CView::checkin();

//get the list of translations made
$translation = new CTranslationOverwrite();
$ds          = $translation->getDS();

$where = [];
if ($source) {
  $where['source'] = $ds->prepareLike("$source%");
}

if ($language) {
  $where['language'] = $ds->prepare('= ?', $language);
}

$total            = $translation->countList($where);
$translations_bdd = $translation->loadList($where, null, "$start,$step");

//load old locales
$locale = CAppUI::pref("LOCALE", "fr");
foreach (CAppUI::getLocaleFilesPaths($locale) as $_path) {
  include $_path;
}

$locales = CMbString::filterEmpty($locales);
foreach ($locales as &$_locale) {
  $_locale = CMbString::unslash($_locale);
}

/** @var CTranslationOverwrite[] $translations_bdd */
foreach ($translations_bdd as $_translation) {
  $_translation->loadOldTranslation($locales);
  $_translation->checkInCache();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("translations_bdd", $translations_bdd);
$smarty->assign("start", $start);
$smarty->assign("step", $step);
$smarty->assign("total", $total);
$smarty->assign("source", $source);
$smarty->assign("language", $language);
$smarty->assign("available_languages", CAppUI::getAvailableLanguages());
$smarty->display("inc_vw_translations.tpl");