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
use Ox\Core\CCanDo;
use Ox\Core\CMbConfig;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Mediboard\System\CCSVImportTranslations;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::checkAdmin();

$translations_ids = CView::post('translations_ids', 'str notNull');

CView::checkin();

if (!CAppUI::conf('debug')) {
  CAppUI::commonError(); // An error occured
}

if (!$translations_ids) {
  CAppUI::commonError('dPdeveloppement-translation-integration no ids');
}

// get each CTranslationOVerwrite id in an array to load them
$translations_ids = explode('|', $translations_ids);

$overwrite    = new CTranslationOverwrite();
$translations = $overwrite->loadAll($translations_ids);

// For each translation we try to find the appropriate module or put it in common
$all_translations = array();
foreach ($translations as $_translation) {
  $tmp = list(, $value, $prefix) = CAppUI::splitLocale($_translation->source);
  $module    = CCSVImportTranslations::getCorrespondingModule($prefix, $value);

  if (!array_key_exists($module, $all_translations)) {
    $all_translations[$module] = array();
  }

  if (!array_key_exists($_translation->language, $all_translations[$module])) {
    $all_translations[$module][$_translation->language] = array();
  }

  $all_translations[$module][$_translation->language][] = $_translation;
}

// Store the translations per module and lang
$langs_to_update = array();
foreach ($all_translations as $_module => $_values) {
  foreach ($_values as $_lang => $_vals) {
    // Store the differents langs to empty cache for them
    if (!in_array($_lang, $langs_to_update)) {
      $langs_to_update[] = $_lang;
    }

    // Get the file to update
    $translate_module          = new CMbConfig();
    $translate_module->options = array("name" => "locales");
    if ($_module == 'common') {
      $translate_module->targetPath = "locales/$_lang/common.php";
    }
    else {
      $translate_module->targetPath = "modules/$_module/locales/$_lang.php";
    }

    $translate_module->sourcePath = $translate_module->targetPath;

    // Create the locales file if needed
    if (!is_file($translate_module->targetPath)) {
      CMbPath::forceDir(dirname($translate_module->targetPath));
      file_put_contents($translate_module->targetPath, '<?php $locales["module-' . $_module . '-court"] = "' . $_module . '";');
    }

    $translate_module->load();


    // Prepare the translations to update or create
    /** @var CTranslationOverwrite $_trans */
    foreach ($_vals as $_trans) {
      if ($_trans->translation !== "") {
        $translate_module->values[$_trans->source] = CMbString::purifyHTML(stripslashes($_trans->translation));
      }
      else {
        unset($translate_module->values[$_trans->source]);
      }
    }

    uksort($translate_module->values, "strnatcmp");

    try {
      if ($translate_module->update($translate_module->values, false)) {
        CAppUI::setMsg("Locales file saved", UI_MSG_OK);

        /** @var CTranslationOverwrite $_trad */
        foreach ($_vals as $_trad) {
          if ($msg = $_trad->delete()) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
          }
        }
      }
    }
    catch (Exception $e) {
      CAppUI::setMsg("Error while saving locales file", UI_MSG_WARNING);
    }
  }
}

Cache::deleteKeys(Cache::OUTER, "locales-$_lang-"); // Remove the keys from SHM

echo CAppUI::getMsg();

CApp::rip();

