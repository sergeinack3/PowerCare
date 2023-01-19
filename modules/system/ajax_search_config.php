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
use Ox\Mediboard\System\CConfigSearch;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkAdmin();

$keywords = CView::get("keywords", 'str notNull');
$start    = CView::get("start", "num default|0");
$step     = CView::get("step", "num default|30");

$configs    = CView::get('configs', 'bool default|0');
$prefs      = CView::get('prefs', 'bool default|0');
$func_perms = CView::get('func_perms', 'bool default|0');

CView::checkin();

if (!$keywords) {
  CAppUI::commonError('system-search-configs no keywords');
}

if (!$configs && !$prefs && !$func_perms) {
  $configs    = 1;
  $prefs      = 1;
  $func_perms = 1;
}

$all_locales = array();

$locales_files = CAppUI::getLocalesFromFiles();

// Configurations
if ($configs) {
  $all_configs = CConfigSearch::getConfigs();
  foreach ($all_configs as $_key => $_type) {
    $_module = null;

    $_first_sep = strpos($_key, " ");
    if ($_first_sep !== false) {
      $_module = substr($_key, 0, $_first_sep);
    }

    $_key = str_replace(" ", "-", $_key);

    $tr       = CAppUI::tr("config-$_key");
    $tr_desc  = CAppUI::tr("config-$_key-desc");
    $old_tr   = isset($locales_files["config-$_key"]) ? $locales_files["config-$_key"] : '';
    $old_desc = isset($locales_files["config-$_key-desc"]) ? $locales_files["config-$_key-desc"] : '';

    $all_locales["config-$_key"] = array(
      "tr"       => $tr,
      "desc"     => $tr_desc,
      "old_tr"   => $old_tr,
      "old_desc" => $old_desc,
      "search"   => CMbString::removeDiacritics("$tr $tr_desc $old_tr $old_desc"),
      "module"   => $_module,
      "type"     => $_type,
      "value"    => '',
    );
  }
}

if ($prefs) {
  // Préferences
  CPreferences::loadModules();
  foreach (CPreferences::$modules as $_module => $_prefs) {
    foreach ($_prefs as $_pref) {
      $tr       = CAppUI::tr("pref-$_pref");
      $tr_desc  = CAppUI::tr("pref-$_pref-desc");
      $old_tr   = isset($locales_files["pref-$_pref"]) ? $locales_files["pref-$_pref"] : '';
      $old_desc = isset($locales_files["pref-$_pref-desc"]) ? $locales_files["pref-$_pref-desc"] : '';

      $all_locales["pref-$_pref"] = array(
        "tr"       => $tr,
        "desc"     => $tr_desc,
        "old_tr"   => $old_tr,
        "old_desc" => $old_desc,
        "search"   => CMbString::removeDiacritics("$tr $tr_desc $old_tr $old_desc"),
        "module"   => $_module,
        "type"     => CConfigSearch::TYPE_CONFIG_PREF,
        "value"    => '',
      );
    }
  }
}

if ($func_perms) {
  // Permissions fonctionnelles
  CPreferences::$modules = array();
  CPreferences::loadModules(true);
  foreach (CPreferences::$modules as $_module => $_prefs) {
    foreach ($_prefs as $_pref) {
      $tr       = CAppUI::tr("pref-$_pref");
      $tr_desc  = CAppUI::tr("pref-$_pref-desc");
      $old_tr   = isset($locales_files["pref-$_pref"]) ? $locales_files["pref-$_pref"] : '';
      $old_desc = isset($locales_files["pref-$_pref-desc"]) ? $locales_files["pref-$_pref-desc"] : '';

      $all_locales["pref-$_pref"] = array(
        "tr"       => $tr,
        "desc"     => $tr_desc,
        "old_tr"   => $old_tr,
        "old_desc" => $old_desc,
        "search"   => CMbString::removeDiacritics("$tr $tr_desc $old_tr $old_desc"),
        "module"   => $_module,
        "type"     => CConfigSearch::TYPE_CONFIG_FUNC_PERM,
        "value"    => '',
      );
    }
  }
}


// Don't warn about unlocalized string (there are too many !)
CAppUI::$unlocalized = array();

$filtered = array();

if ($keywords) {
  $keywords       = CMbString::removeDiacritics($keywords);
  $keywords_split = explode(' ', $keywords);

  $filtered = array_filter(
    $all_locales,
    function ($value) use ($keywords_split) {
      foreach ($keywords_split as $_keyword) {
        if (stripos($value["search"], $_keyword) === false) {
          return false;
        }
      }

      return true;
    }
  );

  uasort(
    $filtered,
    function ($a, $b) {
      return strnatcmp($a["tr"], $b["tr"]);
    }
  );
}

$total        = count($filtered);
$filter_limit = array_splice($filtered, $start, $step);

foreach ($filter_limit as $_key => &$_filtered) {
  $key = substr($_key, strpos($_key, "-") + 1);
  switch ($_filtered['type']) {
    case CConfigSearch::TYPE_CONFIG_INSTANCE:
      $_filtered['value'] = CAppUI::conf(str_replace("-", " ", $key));
      break;
    case CConfigSearch::TYPE_CONFIG_ETAB:
      $_filtered['value'] = CAppUI::conf(str_replace("-", " ", $key), "global");
      break;
    case CConfigSearch::TYPE_CONFIG_PREF:
      $_filtered['value'] = CAppUI::pref($key);
      break;
    case CConfigSearch::TYPE_CONFIG_FUNC_PERM:
      $_filtered['value'] = CAppUI::pref($key);
      break;
    default;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("filtered", $filter_limit);
$smarty->assign("keywords", $keywords);
$smarty->assign("start", $start);
$smarty->assign("step", $step);
$smarty->assign("total", $total);
$smarty->display("inc_search_configs.tpl");