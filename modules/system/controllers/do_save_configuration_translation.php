<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::check();

$feature          = CView::post('feature', 'str notNull');
$translation      = CView::post('translation', 'str');
$translation_desc = CView::post('translation_desc', 'str');

CView::checkin();

if (!$feature) {
    CAppUI::commonError('system-config-translations feature mandatory');
}

$feature_split = explode('-', $feature);

if (!$feature_split[0] == 'config') {
    CAppUI::commonError('system-config-translations not config feature');
}

$module = isset($feature_split[1]) ? $feature_split[1] : '';

if (!$module) {
    CAppUI::commonError();
}

$can = CModule::getCanDo($module);
$can->needsAdmin();

$language = CAppUI::pref("LOCALE", "fr");

$translation      = trim(stripcslashes($translation));
$translation_desc = trim(stripcslashes($translation_desc));

if ($translation) {
    $trans           = new CTranslationOverwrite();
    $trans->language = $language;
    $trans->source   = $feature;
    $trans->loadMatchingObjectEsc();

    if (!$trans->_id || ($trans->_id && $trans->translation != $translation)) {
        $trans->translation = $translation;

        $new = $trans->_id ? 'modify' : 'create';
        if ($msg = $trans->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        } else {
            CAppUI::setMsg('CTranslationOverwrite-msg-' . $new, UI_MSG_OK);
        }
    }
}

if ($translation_desc) {
    $trans           = new CTranslationOverwrite();
    $trans->language = $language;
    $trans->source   = "$feature-desc";
    $trans->loadMatchingObjectEsc();

    if (!$trans->_id || ($trans->_id && $trans->translation != $translation_desc)) {
        $trans->translation = $translation_desc;

        $new = $trans->_id ? 'modify' : 'create';
        if ($msg = $trans->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        } else {
            CAppUI::setMsg('CTranslationOverwrite-msg-' . $new, UI_MSG_OK);
        }
    }
}

Cache::deleteKeys(Cache::OUTER, "locales-$language-");

echo CAppUI::getMsg();

CApp::rip();
