<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;

$nb_files   = round(CValue::get("nb_files", 0));
$date_debut = CValue::get("date_debut");
$date_fin   = CValue::get("date_fin");
$purge      = CValue::get("purge", 0);
$step_from  = CValue::get("step_from", 0);
$file       = new CFile();

if ($date_debut && $date_fin) {
  $where = array();
  $where["file_date"] =  "BETWEEN '".CMbDT::dateTime($date_debut)."' AND '".CMbDT::dateTime($date_fin)."'";
  
  $files = $file->loadList($where, null, "$step_from, 100");
  $count = 0;
  
  foreach ($files as $_file) {
    if (!file_exists($_file->_file_path) || filesize($_file->_file_path) == 0 || file_get_contents($_file->_file_path) == "") {
      if (!$purge) {
        CAppUI::stepAjax($_file->_id);
        $count ++; continue;
      }
      if ($msg = $_file->purge()) {
        CAppUI::stepAjax("File id: " . $_file->_id . " - " . $_file->purge());
      }
      else {
        $count++;
      }
    }
  }
  if ($purge) {
    CAppUI::stepAjax("$count fichiers supprimés");
  }
  else {
    CAppUI::stepAjax("$count fichiers à traiter");
  }
}
else {
  $file->doc_size = 0;
  $nb_files_size_zero = $file->countMatchingList();
  if ($nb_files == 0) {
    CAppUI::stepAjax("Nombre de fichiers avec une taille de 0 octets : " . $nb_files_size_zero);  
  }
  else {
    $where = array();
    $where["doc_size"] = " = '0'";

    /** @var CFile[] $files */
    $files = $file->loadList($where, null, $nb_files);
    
    if (count($files) == 0 ) {
      CAppUI::stepAjax("Aucun fichier à traiter");
    }
    else {
      foreach ($files as &$_file) {
        if (!file_exists($_file->_file_path) || filesize($_file->_file_path) === 0) {
          CAppUI::stepAjax("File id : " . $_file->_id . " - non existant ou vide - Suppression :" . $_file->delete());
        }
        else {
          $_file->doc_size = filesize($_file->_file_path);
          CAppUI::stepAjax("File id : $_file->_id - mise à jour de la taille ({$_file->doc_size} octets)- Update :".$_file->store());
        }
      }
    }
  }
}
