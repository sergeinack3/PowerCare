<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbLock;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CMouvement400;
use Ox\Mediboard\Sante400\CMouvFactory;
use Ox\Mediboard\Sante400\CRecordSante400;

CCanDo::checkEdit();

CApp::setTimeLimit(90);

CRecordSante400::$verbose = CValue::get("verbose");

$types = CMouvFactory::getTypes();
if (!count($types)) {
  CAppUI::stepMessage(UI_MSG_WARNING, "CMouvFactory-warning-noclasses");

  return;
}

$marked = CValue::getOrSession("marked", "1");
$max    = CValue::get("max", CAppUI::conf("sante400 nb_rows"));

// Load mouvements
$class = CValue::get("class");
$type  = CValue::getOrSession("type");
$mouvs = array();
$count = 0;
$procs = 0;

if (!in_array($type, $types)) {
  $type = null;
}

// Mouvement type (or class) provided
if ($type || $class) {
  // Mouvement construction by factory
  $mouv = $class ? new $class : CMouvFactory::create($type);

  if (!$mouv) {
    CAppUI::stepMessage(UI_MSG_ERROR, "CMouvFactory-error-noclass", CValue::first($type, $class));

    return;
  }

  // Initialisation d'un fichier de verrou
  $class = $mouv->class;
  $lock  = new CMbLock("synchro_sante400/{$class}");

  $request_uid = CApp::getRequestUID();

  // Mouvements loading
  /** @var CMouvement400[] $mouvs */
  $mouvs = array();
  if ($rec = CValue::get("rec")) {
    try {
      $mouv->load($rec);
      $mouvs = array($mouv);
    } catch (Exception $e) {
      trigger_error("Mouvement with id '$rec' has been deleted : " . $e->getMessage(), E_USER_ERROR);
    }
  } else {
    // On tente de verrouiller seuement pour les traitements de masse
    $_t = microtime(true);

    if (!$lock->acquire()) {
      $_lock_duration = intval((microtime(true) - $_t) * 1000);

      $lock->failedMessage();

      $_message = sprintf(
        "[%s] Trigger '%s' skipped by lock in %d ms", $request_uid, $type, $_lock_duration
      );
      CApp::log($_message);

      return;
    }

    // Mouvements counting
    $_t              = microtime(true);
    $count           = $mouv->count($marked);
    $_count_duration = intval((microtime(true) - $_t) * 1000);

    $_t                 = microtime(true);
    $mouvs              = $mouv->loadList($marked, $max);
    $_loadlist_duration = intval((microtime(true) - $_t) * 1000);

    $_message = sprintf(
      "[%s] %d triggers '%s' loaded in %d ms among %d counted in %d ms", $request_uid, count($mouvs), $type, $_loadlist_duration, $count, $_count_duration
    );
    CApp::log($_message);
  }

  $_durations = array();
  // Proceed mouvements
  foreach ($mouvs as $_mouv) {
    $_t = microtime(true);

    if ($_mouv->proceed()) {
      $procs++;
    }

    $_durations[] = round((microtime(true) - $_t) * 1000, 2);
  }

  if ($_durations) {
    $_msg = sprintf(
      "[%s] %d triggers '%s' processed in %d ms", $request_uid, $procs, $type, round(array_sum($_durations), 2)
    );

    if ($_durations) {
      $min    = min($_durations);
      $avg    = round(array_sum($_durations) / count($_durations), 2);
      $max    = max($_durations);
      $stddev = round(CMbArray::variance($_durations), 2);

      $_msg .= sprintf(
        " (min/avg/max/stddev = %s/%s/%s/%s ms)",
        $min, $avg, $max, $stddev
      );
    }
  } else {
    $_msg = sprintf(
      "[%s] No triggers '%s' processed", $request_uid, $type
    );
  }

  CApp::log("Log from synchro_sante400", $_msg);

    $lock->release();

  // Purge
  CApp::doProbably(
    100,
    function () use ($mouv) {
      $mouv->purgeSome(false);
    }
  );
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("connection", CRecordSante400::$dbh);
$smarty->assign("type", $type);
$smarty->assign("class", $class);
$smarty->assign("types", $types);
$smarty->assign("marked", $marked);
$smarty->assign("count", $count);
$smarty->assign("procs", $procs);
$smarty->assign("mouvs", $mouvs);
$smarty->assign("relaunch", CValue::get("relaunch"));

$smarty->display("synchro_sante400.tpl");
