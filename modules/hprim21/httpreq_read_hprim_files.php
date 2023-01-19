<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Interop\Ftp\CFTP;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Hprim21\CEchangeHprim21;
use Ox\Interop\Hprim21\CHPrim21Reader;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Lecture des fichiers d'échanges Hprim21
 */
CCanDo::checkRead();

// Envoi à la source créée 'HPRIM21' (FTP)
$group_id    = CGroups::loadCurrent()->_id;
$source_name = "hprim21-$group_id";

/** @var CSourceFTP $exchange_source */
$exchange_source = CExchangeSource::get($source_name, CSourceFTP::TYPE);
$extension       = $exchange_source->fileextension;

$ftp = new CFTP();
$ftp->init($exchange_source);

try {
    $ftp->connect();
} catch (CMbException $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
}

$list = [];
try {
    $list = $ftp->getListFiles($ftp->fileprefix);
} catch (CMbException $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
}

if (empty($list)) {
    CAppUI::stepAjax("Le répertoire ne contient aucun fichier", UI_MSG_ERROR);
}

$sender_ftp          = new CSenderFTP();
$sender_ftp->user_id = CUser::get()->_id;
$sender_ftp->loadMatchingObject();

$count = CAppUI::conf("eai max_files_to_process");
$list  = array_slice($list, 0, $count);

foreach ($list as $filepath) {
    if (substr($filepath, -(strlen($extension))) == $extension) {
        $filename  = basename($filepath);
        $hprimFile = "tmp/hprim21/$filename";
        $ftp->getData($filepath, $hprimFile);

        // Création de l'échange
        $echg_hprim21                  = new CEchangeHprim21();
        $echg_hprim21->group_id        = $group_id;
        $echg_hprim21->sender_class    = $sender_ftp->_class;
        $echg_hprim21->sender_id       = $sender_ftp->_id;
        $echg_hprim21->date_production = CMbDT::dateTime();
        $echg_hprim21->store();

        $hprimReader                   = new CHPrim21Reader();
        $hprimReader->_echange_hprim21 = $echg_hprim21;
        $hprimReader->readFile($hprimFile);

        // Mapping de l'échange
        $echg_hprim21 = $hprimReader->bindEchange($hprimFile);

        if (!count($hprimReader->error_log)) {
            $echg_hprim21->message_valide = true;

            // legacy action
            $ftp->_destination_file = ".";
            $ftp->delFile($filepath);
        } else {
            $echg_hprim21->message_valide = false;
            CAppUI::stepAjax("Erreur(s) pour le fichier '$filepath'", UI_MSG_WARNING);
            CApp::log('hprim files reader error', $hprimReader->error_log);
        }
        $msg = $echg_hprim21->store();
        $msg ? CAppUI::stepAjax("Erreur lors de la création de l'échange : $msg", UI_MSG_WARNING) :
            CAppUI::stepAjax("L'échange '$echg_hprim21->_id' a été créé.");
        unlink($hprimFile);
    } else {
        // legacy action
        $ftp->_destination_file = '.';
        $ftp->delFile($filepath);
    }
}
