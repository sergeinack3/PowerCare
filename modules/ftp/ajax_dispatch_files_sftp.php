<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Mutex\CMbMutex;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Ftp\CSourceSFTP;

/**
 * Receive files EAI
 */
CCanDo::checkRead();

CApp::setTimeLimit(240);
CApp::setMemoryLimit("1024M");

$actor_guid   = CView::get("actor_guid", "guid class|CInteropActor");
$to_treatment = CView::get("to_treatment", "bool default|1");
$trace        = CView::get("trace", "bool default|0");
CView::checkin();

/** @var CSenderSFTP $sender */
$sender = CMbObject::loadFromGuid($actor_guid);
$sender->loadRefGroup();
$sender->loadRefsExchangesSources();

// Si pas actif
if (!$sender->actif || ($sender->role != CAppUI::conf("instance_role"))) {
    CAppUI::stepAjax("CInteropSender-msg-Incompatible role or inactive sender", UI_MSG_ERROR);
}

/** @var CSourceSFTP $source */
$source = reset($sender->_ref_exchanges_sources);
if (!$source->active || ($source->role != CAppUI::conf("instance_role"))) {
    CAppUI::stepAjax("CExchangeSource-msg-Incompatible role or inactive source", UI_MSG_ERROR);
}

// Initialisation d'un fichier de verrou de 240 secondes
$lock = new CMbMutex("dispatch-filesftp-$sender->_guid");
if (!$lock->lock(240)) {
    return;
}

$files = [];
try {
    $files = $source->getClient()->receive();
} catch (CMbException $e) {
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
$delete_file             = $sender->_delete_file;

$sender->_delete_file = $delete_file;

foreach ($files as $_filepath) {
    $path_info = pathinfo($_filepath);
    if (!isset($path_info["extension"])) {
        continue;
    }

    $extension = $path_info["extension"];

    // Cas où l'extension voulue par la source sFTP est différente du fichier
    if ($fileextension && ($extension != $fileextension)) {
        continue;
    }

    $path             = rtrim($path_info["dirname"], "\\/");
    $filename         = $path_info["filename"];
    $_filepath_no_ext = $filename;
    if ($path != ".") {
        $_filepath_no_ext = "$path/$filename";
    }

    // Cas où le suffix de l'acq OK est présent mais que je n'ai pas de fichier
    // d'acquittement dans le dossier
    if ($fileextension_write_end && count(preg_grep("@$_filepath_no_ext.$fileextension_write_end$@", $files)) == 0) {
        continue;
    }

    $basename = $path_info["basename"];
    if (!$basename) {
        continue;
    }


    try {
        $message = $source->getClient()->getData($basename);
        if (!$message) {
            continue;
        }
    } catch (CMbException $e) {
        CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);

        $e->stepAjax(UI_MSG_WARNING);
        continue;
    }

    $source->_receive_filename = $path_info["filename"];

    if ($trace) {
        dump($basename);
    }

    // Dispatch EAI
    try {
        if ($acq = CEAIDispatcher::dispatch($message, $sender, null, $to_treatment)) {
            // Pas de config pour la création de l'acq
            // CEAIDispatcher::createFileACK($acq, $sender);
        }
    } catch (CMbException $e) {
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
    } catch (CMbException $e) {
        CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        $e->stepAjax();
        continue;
    }

    CAppUI::stepAjax("CEAIDispatcher-message_dispatch");
}

// Libère le verrou
$lock->release();
