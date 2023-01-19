<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Edit Translation
 */
CCanDo::checkEdit();

$translation_id = CView::get('trad_id', 'str');
$language       = CView::get('language', 'str default|' . CAppUI::pref("LOCALE", "fr"));
$source         = CView::get('source', 'str');

CView::checkin();

$translation = new CTranslationOverwrite();

if ($translation_id) {
  if ($translation->load($translation_id)) {
    $translation->loadOldTranslation();
  }
}

if ($source) {
  $translation->source           = $source;
  $translation->_old_translation = CAppUI::tr($source);
  $translation->loadMatchingObject();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("translation", $translation);
$smarty->assign("language", $language);
$smarty->assign("languages", CAppUI::getAvailableLanguages());
$smarty->display("inc_edit_translation.tpl");