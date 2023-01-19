<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Mutex\CMbMutex;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSourceFTP;

/**
 * Receive files EAI
 */
CCanDo::checkRead();

$actor_guid   = CValue::get("actor_guid");
$to_treatment = CValue::get("to_treatment", 1);

/** @var CSenderFTP $sender */
$sender = CMbObject::loadFromGuid($actor_guid);
$sender->loadRefGroup();
$sender->loadRefsExchangesSources();

// Si pas actif
if (!$sender->actif || ($sender->role != CAppUI::conf("instance_role"))) {
    CAppUI::stepAjax("CInteropSender-msg-Incompatible role or inactive sender", UI_MSG_ERROR);
}

/** @var CSourceFTP $source */
$source = reset($sender->_ref_exchanges_sources);
if (!$source->active || ($source->role != CAppUI::conf("instance_role"))) {
    CAppUI::stepAjax("CExchangeSource-msg-Incompatible role or inactive source", UI_MSG_ERROR);
}

// Initialisation d'un fichier de verrou de 240 secondes
$lock = new CMbMutex("dispatch-filesftp-$sender->_guid");
if (!$lock->lock(240)) {
    return;
}

$files = array();
try {
    $files = $source->getClient()->receive();
} catch (CMbException $e) {
    $e->stepAjax();

    // Libère le verrou
    $lock->release();
    return;
}

$fileextension           = $source->fileextension;
$fileextension_write_end = $source->fileextension_write_end;

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

    $_old_basename = $basename;
    $basename      = "$basename.checkedout";
    $source->getClient()->renameFile($_old_basename, $basename);

    try {
        $message = $source->getClient()->getData($basename);
        if (!$message) {
            continue;
        }
    }
    catch (CMbException $e) {
        $source->getClient()->renameFile($basename, $_old_basename);

        $e->stepAjax(UI_MSG_WARNING);
        continue;
    }

    $source->_receive_filename = $path_info["filename"];

    // Dispatch EAI
    if ($acq = CEAIDispatcher::dispatch($message, $sender, null, $to_treatment)) {
        try {
            CEAIDispatcher::createFileACK($acq, $sender);
        }
        catch (CMbException $e) {
            if ($sender->_delete_file !== false) {
                $source->getClient()->delFile($basename);

                if ($fileextension_write_end) {
                    $source->getClient()->delFile("$filename.$fileextension_write_end");
                }
            }
            else {
                CAppUI::stepAjax("CEAIDispatcher-error_deleting_file", UI_MSG_WARNING);
            }
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }
    }

    if (!$sender->_delete_file) {
        // Rename file
        $source->getClient()->renameFile($basename, $_old_basename);

        CAppUI::stepAjax("CEAIDispatcher-message_dispatch");
        continue;
    }

    try {
        if ($sender->_delete_file !== false) {
            $source->getClient()->delFile($basename);
            if ($fileextension_write_end) {
                $source->getClient()->delFile("$filename.$fileextension_write_end");
            }
        }
        else {
            CAppUI::stepAjax("CEAIDispatcher-error_deleting_file", UI_MSG_WARNING);
        }
    }
    catch (CMbException $e) {
        $e->stepAjax(UI_MSG_WARNING);
        continue;
    }

    CAppUI::stepAjax("Message retraité");
}

// Libère le verrou
$lock->release();
return;
