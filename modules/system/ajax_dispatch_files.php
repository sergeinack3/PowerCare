<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Mutex\CMbMutex;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CSenderFileSystem;
use Ox\Mediboard\System\CSourceFileSystem;

/**
 * Receive files EAI
 */
CCanDo::checkRead();

CApp::setTimeLimit(240);
CApp::setMemoryLimit("1024M");

$actor_guid   = CView::get("actor_guid"  , "guid class|CInteropActor");
$to_treatment = CView::get("to_treatment", "bool default|1");
$trace        = CView::get("trace"       , "bool default|0");
CView::checkin();

/** @var CSenderFileSystem $sender */
$sender = CMbObject::loadFromGuid($actor_guid);
$sender->loadRefGroup();
$sender->loadRefsExchangesSources();

// Si pas actif
if (!$sender->actif || ($sender->role != CAppUI::conf("instance_role"))) {
  CAppUI::stepAjax("CInteropSender-msg-Incompatible role or inactive sender", UI_MSG_ERROR);
}

/** @var CSourceFileSystem $source */
$source = reset($sender->_ref_exchanges_sources);
if (!$source->active || ($source->role != CAppUI::conf("instance_role"))) {
  CAppUI::stepAjax("CExchangeSource-msg-Incompatible role or inactive source", UI_MSG_ERROR);
}

$path = $source->getFullPath($source->_path);
$filename_excludes = "$path/mb_excludes.txt";

// Initialisation d'un fichier de verrou de 240 secondes
$lock = new CMbMutex("dispatch-files-$sender->_guid");
// On tente de verrouiller
if (!$lock->lock(240)) {
  return;
}

$count = $source->_limit = CAppUI::conf("eai max_files_to_process");

$files = array();
try {
  $files = $source->getClient()->receive();
}
catch (CMbException $e) {
  CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_ERROR);

  $e->stepAjax();

  // Libère le verrou
  $lock->release();
  return;
}
if ($trace) {
  dump($files);
}

$fileextension           = $source->fileextension;
$fileextension_write_end = $source->fileextension_write_end;

$files_excludes = array();
if (file_exists($filename_excludes)) {
  $files_excludes = array_map('trim', file($filename_excludes));
}

$array_diff = array_diff($files_excludes, $files);

$files = array_diff($files, array($filename_excludes));
$files = array_diff($files, $files_excludes);
$files = array_slice($files, 0, $count);

$array_diff = array_diff($files_excludes, $array_diff);

// Nettoyage du fichier si besoin
$nb_files_retention_mb_excludes = CAppUI::conf("eai nb_files_retention_mb_excludes");
$files_to_keep = $array_diff;
$array_chunk   = array();
if (count($array_diff) > $nb_files_retention_mb_excludes) {
  $array_chunk   = array_chunk($array_diff, $nb_files_retention_mb_excludes);
  $files_to_keep = CMbArray::get($array_chunk, 0);
}

// Suppression des fichiers en trop
if ($files_to_be_deleted = CMbArray::get($array_chunk, 1)) {
  foreach ($files_to_be_deleted as $_file_to_be_deleted) {
    if (file_exists($_file_to_be_deleted)) {
      unlink($_file_to_be_deleted);
    }
  }
}

// Mise à jour du fichier mb_excludes avec le nouveau diff
if (file_exists($filename_excludes)) {
  unlink($filename_excludes);
}
$file  = fopen($filename_excludes, "a+");
foreach ($files_to_keep as $_file_exclude) {
  fputs($file, $_file_exclude."\n");
}

if (empty($files)) {
  CAppUI::stepAjax("CEAIDispatcher-no-file", UI_MSG_WARNING);

  // Libère le verrou
  $lock->release();
  return;
}

$sender->_delete_file = $sender->after_processing_action == "delete";

foreach ($files as $_filepath) {
  $path_info = pathinfo($_filepath);
  if (!isset($path_info["extension"])) {
    continue;
  }

  $extension = $path_info["extension"];

  // Cas où l'extension voulue par la source FS est différente du fichier
  if ($fileextension && ($extension != $fileextension)) {
    continue;
  }

  $path = rtrim($path_info["dirname"], "\\/");
  $_filepath_no_ext = "$path/".$path_info["filename"];

  // Cas où le suffix de l'acq OK est présent mais que je n'ai pas de fichier 
  // d'acquittement dans le dossier
  if ($fileextension_write_end && count(preg_grep("@$_filepath_no_ext.$fileextension_write_end$@", $files)) == 0) {
    continue;
  }

  //$_old_filepath = $_filepath;
  //$_filepath     = "$_filepath.checkedout";
  //$source->renameFile($_old_filepath, $_filepath);

  try {
    $message  = $source->getClient()->getData($_filepath);
    if (!$message) {
      continue;
    }
  }
  catch (CMbException $e) {
    //$source->renameFile($_filepath, $_old_filepath);

    CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
    $e->stepAjax(UI_MSG_WARNING);
    continue;
  }

  $source->_receive_filename = $path_info["filename"];

  if ($trace) {
    dump($_filepath);
  }

  // Dispatch EAI
  try {
    if ($acq = CEAIDispatcher::dispatch($message, $sender, null, $to_treatment)) {
      CEAIDispatcher::createFileACK($acq, $sender);
    }
  }
  catch (CMbException $e) {
    dispatchError($sender, $filename_excludes, $path_info);
    if ($sender->_delete_file) {
      $source->getClient()->delFile($_filepath);
      if ($fileextension_write_end) {
        $source->getClient()->delFile("$_filepath_no_ext.$fileextension_write_end");
      }
    }

    CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_ERROR);
    $e->stepAjax();
    continue;
  }

  if (!$sender->_delete_file) {
    CAppUI::stepAjax("CEAIDispatcher-message_dispatch");

    continue;
  }

  try {
    $source->getClient()->delFile($_filepath);
    if ($fileextension_write_end) {
      $source->getClient()->delFile("$_filepath_no_ext.$fileextension_write_end");
    }
  }
  catch (CMbException $e) {
    CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
    $e->stepAjax();
    continue;
  }

  CAppUI::stepAjax("CEAIDispatcher-message_dispatch");
}

fclose($file);

// Libère le verrou
$lock->release();

/**
 * Dispatch error
 *
 * @param CInteropSender $sender            Sender
 * @param string         $filename_excludes Files excludes
 * @param string         $path_info         Information about a file path
 *
 * @return void
 */
function dispatchError(CInteropSender $sender, $filename_excludes, $path_info) {
  $message = null;
  if ($sender->_data_format) {
    $message = $sender->_data_format->_event_message->code;
  }

  CAppUI::stepAjax("CEAIDispatcher-no_message_supported_for_this_actor", UI_MSG_WARNING, $message);

  $file  = fopen($filename_excludes, "a");
  fputs($file, $path_info["dirname"]."/".$path_info["basename"]."\n");
  fclose($file);
}
