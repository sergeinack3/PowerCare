<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Ftp\CFTP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Reader;
use Ox\Mediboard\System\CExchangeSource;

CCanDo::checkRead();

// Envoi à la source créée 'HL7 v.2'
$exchange_source = CExchangeSource::get("hl7v2", CSourceFTP::TYPE);
$extension = $exchange_source->fileextension;

$ftp = new CFTP();
$ftp->init($exchange_source);
$ftp->connect();

if (!$list = $ftp->getListFiles($ftp->fileprefix)) {
  CAppUI::stepAjax("Le répertoire ne contient aucun fichier", UI_MSG_ERROR);
}

$messages = array();

foreach ($list as $filepath) {
  if (substr($filepath, -(strlen($extension))) == $extension) {
    $filename = tempnam("", "hl7");
    $ftp->getData($filepath, $filename);
    $hl7v2_reader = new CHL7v2Reader();
    
    $message = $hl7v2_reader->readFile($filename);
    
    if (!$message) {
      $message = new CHL7v2Message;
    }
    
    $message->filename = basename($filepath);
    
    $message->_errors_msg   = !$message->isOK(CHL7v2Error::E_ERROR);
    $message->_warnings_msg = !$message->isOK(CHL7v2Error::E_WARNING);
    $message->_xml = $message->toXML()->saveXML();
    
    $messages[] = $message;
    
    unlink($filename);
  } else {
   // $ftp->delFile($filepath);
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("messages", $messages);
$smarty->display("inc_read_hl7v2_files.tpl");
