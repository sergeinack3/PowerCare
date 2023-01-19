<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::check();

$feature = CView::get('feature', 'str notNull');

CView::checkin();

if (!$feature) {
  CAppUI::commonError('system-config-translations feature mandatory');
}

$feature       = str_replace(' ', '-', $feature);
$feature_split = explode('-', $feature);
$module        = $feature_split[1];

$module = isset($feature_split[1]) ? $feature_split[1] : '';

if (!$module) {
  CAppUI::commonError();
}

$can = CModule::getCanDo($module);
$can->needsAdmin();

$language = CAppUI::pref("LOCALE", "fr");

$locales = CAppUI::getLocalesFromFiles();

$trans_bdd           = new CTranslationOverwrite();
$trans_bdd->language = $language;
$trans_bdd->source   = $feature;
$trans_bdd->loadMatchingObjectEsc();

if ($trans_bdd && $trans_bdd->_id) {
  $trans_bdd->loadOldTranslation($locales);

}
elseif (isset($locales[$trans_bdd->source])) {
  $trans_bdd->_old_translation = $locales[$trans_bdd->source];
}

$trans_bdd_desc           = new CTranslationOverwrite();
$trans_bdd_desc->language = $language;
$trans_bdd_desc->source   = "$feature-desc";
$trans_bdd_desc->loadMatchingObjectEsc();

if ($trans_bdd_desc && $trans_bdd_desc->_id) {
  $trans_bdd_desc->loadOldTranslation($locales);
}
elseif (isset($locales[$trans_bdd_desc->source])) {
  $trans_bdd_desc->_old_translation = $locales[$trans_bdd_desc->source];
}

$smarty = new CSmartyDP();
$smarty->assign('feature', $feature);
$smarty->assign('trans_bdd', $trans_bdd);
$smarty->assign('trans_bdd_desc', $trans_bdd_desc);
$smarty->display('vw_translations_config');
