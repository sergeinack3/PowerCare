<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbConfig;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Redirect to the last page
 *
 * @return void
 */
function redirect()
{
    if (CValue::post("ajax")) {
        echo CAppUI::getMsg();
        CApp::rip();
    }

    $m   = CValue::post("m");
    $tab = CValue::post("tab");
    CAppUI::redirect("m=$m&tab=$tab");
}

global $can;

$user = CUser::get();

// only user_type of Administrator (1) can access this page
$can->edit |= ($user->user_type != 1);
$can->needsEdit();

$module_name = CView::post("module", "str notNull");
$strings     = CView::post("s", "str"); // Actually array
$language    = CView::post("language", "enum list|fr|de|en|fr-de|nb-be|it");
$trad_key    = CView::post('key', 'str');
$trad_value  = CView::post('value', 'str');

CView::checkin();

if (!$module_name || !$trad_key && (!$strings || !is_array($strings))) {
    CAppUI::setMsg("Certaines informations sont manquantes au traitement de la traduction.", UI_MSG_ERROR);
    redirect();

    return;
}

if (!$strings && $trad_key) {
    $strings = [
        $trad_key => $trad_value,
    ];
}

// Redirect the translations to the CTranslationOverwrite in DB
if (!CAppUI::conf('debug')) {
    foreach ($strings as $_key => $_value) {
        if (!trim($_value)) {
            continue;
        }

        $trans           = new CTranslationOverwrite();
        $trans->language = $language;
        $trans->source   = trim($_key);
        $trans->loadMatchingObjectEsc();

        if (!$trans->_id && (CAppUI::localExists($_key, $_value, true))) {
            continue;
        }

        $trans->translation = trim(str_replace('\\', '', $_value));
        $new                = ($trans->_id) ? 'modify' : 'create';

        if ($msg = $trans->store()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        } else {
            CAppUI::setMsg("CTranslationOverwrite-msg-$new", UI_MSG_OK);
        }
    }

    echo CAppUI::getMsg();

    redirect();
}

$translateModule             = new CMbConfig();
$translateModule->sourcePath = null;

// Ecriture du fichier
$translateModule->options = ["name" => "locales"];

if ($module_name != "common") {
    $translateModule->targetPath = "modules/$module_name/locales/$language.php";
} else {
    $translateModule->targetPath = "locales/$language/common.php";
}

$translateModule->sourcePath = $translateModule->targetPath;

if (!is_file($translateModule->targetPath)) {
    CMbPath::forceDir(dirname($translateModule->targetPath));
    file_put_contents(
        $translateModule->targetPath,
        '<?php $locales["module-' . $module_name . '-court"] = "' . $module_name . '";'
    );
}

$translateModule->load();

if (!$strings && $trad_key) {
    $strings = [
        $trad_key => $trad_value,
    ];
}

foreach ($strings as $key => $valChaine) {
    if ($valChaine !== "") {
        $translateModule->values[$key] = CMbString::purifyHTML(stripslashes($valChaine));
    } else {
        unset($translateModule->values[$key]);
    }
}

uksort($translateModule->values, "strnatcmp");

try {
    if ($translateModule->update($translateModule->values, false)) {
        Cache::deleteKeys(Cache::OUTER, "locales-$language-");
        CAppUI::setMsg("Locales file saved", UI_MSG_OK);
        redirect();
    }
} catch (Exception $e) {
    Cache::deleteKeys(Cache::OUTER, "locales-$language-");
    CAppUI::setMsg("Error while saving locales file : {$e->getMessage()}", UI_MSG_ERROR);
}
