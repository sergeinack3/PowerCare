<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbPath;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;

/**
 * CFile controller
 */
class CFileAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CFile", "file_id");

    global $m;    
    $this->redirect = "m=$m"; 

    if ($dialog = CValue::read($this->request, "dialog")) {
      $this->redirect      .= "&a=upload_file&dialog=1";
      $this->redirectStore = "m=$m&a=upload_file&dialog=1&uploadok=1";
    }
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    $upload     = null;

    $_file_category_id = CValue::read($this->request, "_file_category_id");
    $_ext_cabinet_id   = CValue::read($this->request, "_ext_cabinet_id");
    $language          = CValue::read($this->request, "language");
    $type_doc_dmp      = CValue::read($this->request, "type_doc_dmp");
    $type_doc_sisra    = CValue::read($this->request, "type_doc_sisra");
    $named             = CValue::read($this->request, "named");
    $rename            = CValue::read($this->request, "_rename");
    $object_class      = CValue::read($this->request, "object_class");
    $object_id         = CValue::read($this->request, "object_id");
    $private           = CValue::read($this->request, "private");
    $send              = CValue::read($this->request, "send");
    $masquage_patient  = CValue::read($this->request, "masquage_patient");
    $masquage_praticien = CValue::read($this->request, "masquage_praticien");
    $masquage_representants_legaux = CValue::read($this->request, "masquage_representants_legaux");

    CValue::setSession("_rename", $rename);

    if (isset($_FILES["formfile"])) {
      $aFiles = array();
      $upload =& $_FILES["formfile"];

      foreach ($upload["error"] as $fileNumber => $etatFile) {
        if (!$named) {
          $rename = $rename ? $rename . strrchr($upload["name"][$fileNumber], '.') : "";
        }

        if ($upload["name"][$fileNumber]) {
          $file_name = $upload["name"][$fileNumber];

          if ($this->ajax) {
            $file_name = fixISOEncoding(CMbString::normalizeUtf8($file_name));
          }
          
          $aFiles[] = array(
            "_mode"            => "file",
            "name"             => $file_name,
            "type"             => CMbPath::guessMimeType($upload["name"][$fileNumber]),
            "tmp_name"         => $upload["tmp_name"][$fileNumber],
            "error"            => $upload["error"][$fileNumber],
            "size"             => $upload["size"][$fileNumber],
            "language"         => $language,
            "type_doc_dmp"     => $type_doc_dmp,
            "type_doc_sisra"   => $type_doc_sisra,
            "file_category_id" => $_file_category_id,
            "_ext_cabinet_id"  => $_ext_cabinet_id,
            "object_id"        => $object_id,
            "object_class"     => $object_class,
            "_rename"          => $rename,
          );
        }
      }

      $merge_files = CValue::read($this->request, "_merge_files");

      if ($merge_files) {
        $pdf = new CMbPDFMerger();

        $this->_obj = new $this->_obj->_class;

        /** @var CFile $obj */
        $obj = $this->_obj;
        $file_name = "";
        $nb_converted = 0;

        foreach ($aFiles as $key => $file) {
          $converted = 0;
          if ($file["error"] == UPLOAD_ERR_NO_FILE) {
            continue;
          }

          if ($file["error"] != 0) {
            CAppUI::setMsg(CAppUI::tr("CFile-msg-upload-error-".$file["error"]), UI_MSG_ERROR);
            continue;
          }

          if (!$file["size"]) {
            CAppUI::setMsg(CAppUI::tr("Unsent"), UI_MSG_ERROR);
            continue;
          }

          // Si c'est un pdf, on le rajoute sans aucun traitement
          if (substr(strrchr($file["name"], '.'), 1) == "pdf") {
            $file_name .= substr($file["name"], 0, strpos($file["name"], '.'));
            $pdf->addPDF($file["tmp_name"], 'all');
            $nb_converted ++;
            $converted = 1;
          }
          // Si le fichier est convertible en pdf
          else if ($obj->isPDFconvertible($file["name"]) && $obj->convertToPDF($file["tmp_name"], $file["tmp_name"]."_converted")) {
            $pdf->addPDF($file["tmp_name"]."_converted", 'all'); 
            $file_name .= substr($file["name"], 0, strpos($file["name"], '.'));
            $nb_converted ++;
            $converted = 1;
          }
          // Sinon création d'un cfile
          else {
            $other_file = new CFile();
            $other_file->bind($file);
            $other_file->file_name = $file["name"];
            $other_file->file_type = $file["type"];
            $other_file->doc_size = $file["size"];
            $other_file->fillFields();
            $other_file->private = $private;
            $other_file->send = $send;
            $other_file->masquage_patient = $masquage_patient;
            $other_file->masquage_praticien = $masquage_praticien;
            $other_file->masquage_representants_legaux = $masquage_representants_legaux;

            $other_file->setMoveTempFrom($file['tmp_name']);

            $other_file->author_id = CAppUI::$user->_id;

            if ($msg = $other_file->store()) {
              CAppUI::setMsg("Fichier non enregistré: $msg", UI_MSG_ERROR);
              continue;
            }

            CAppUI::setMsg("Fichier enregistré", UI_MSG_OK);
          }
          // Pour le nom du pdf de fusion, on concatène les noms des fichiers
          if ($key != count($aFiles)-1 && $converted) {
            $file_name .= "-";
          }
        }

        // Si des fichiers ont été convertis et ajoutés à PDFMerger,
        // création du cfile.
        if ($nb_converted) {
          $obj->file_name = $file_name.".pdf";
          $obj->file_type = "application/pdf";
          $obj->author_id = CAppUI::$user->_id;
          $obj->object_id = $object_id;
          $obj->object_class = $object_class;
          $obj->private = $private;
          $obj->send = $send;
          $obj->masquage_patient = $masquage_patient;
          $obj->masquage_praticien = $masquage_praticien;
          $obj->masquage_representants_legaux = $masquage_representants_legaux;
          $obj->updateFormFields();
          $obj->fillFields();

          $tmpname = tempnam("/tmp", "pdf_");
          $pdf->merge('file', $tmpname);

          $obj->doc_size = strlen(file_get_contents($tmpname));
          $obj->setMoveFrom($tmpname);

          if ($msg = $obj->store()) {
            CAppUI::setMsg("Fichier non enregistré: $msg", UI_MSG_ERROR);
          }
          else {
            CAppUI::setMsg("Fichier enregistré", UI_MSG_OK);
          }
        }
      }
      else {
        foreach ($aFiles as $file) {
          if ($file["error"] == UPLOAD_ERR_NO_FILE) {
            continue;
          }

          if ($file["error"] != 0) {
            CAppUI::setMsg(CAppUI::tr("CFile-msg-upload-error-".$file["error"]), UI_MSG_ERROR);
            continue;
          }

          if (!$file["size"]) {
            CAppUI::setMsg(CAppUI::tr("Unsent"), UI_MSG_ERROR);
            continue;
          }

          // Reinstanciate

          $this->_obj = new $this->_obj->_class;

          /** @var CFile $obj */
          $obj = $this->_obj;
          $obj->bind($file);
          $obj->file_name = empty($file["_rename"]) ? $file["name"] : $file["_rename"];
          $obj->file_type = $file["type"];

          if ($obj->file_type === "application/x-download") {
            $obj->file_type = CMbPath::guessMimeType($obj->file_name);
          }

          $obj->doc_size = $file["size"];
          $obj->fillFields();
          $obj->private   = $private;
          $obj->send      = $send;
          $obj->masquage_patient  = $masquage_patient;
          $obj->masquage_praticien  = $masquage_praticien;
          $obj->masquage_representants_legaux  = $masquage_representants_legaux;

          if ($file["_mode"] === "file") {
            $obj->setMoveTempFrom($file['tmp_name']);
          }
          else {
            $obj->setMoveFrom($file["tmp_name"]);
          }

          // File owner on creation
          if (!$obj->file_id) {
            $obj->author_id = CAppUI::$user->_id;
          }

          if ($msg = $obj->store()) {
            CAppUI::setMsg("Fichier non enregistré: $msg", UI_MSG_ERROR);
            continue;
          }

          CAppUI::setMsg("Fichier enregistré", UI_MSG_OK);
        }
      }
      // Redaction du message et renvoi
      if (CAppUI::isMsgOK() && $this->redirectStore) {
        $this->redirect =& $this->redirectStore;
      }

      if (!CAppUI::isMsgOK() && $this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    }
    else {
      parent::doStore();
    }
  }

  /**
   * @inheritdoc
   *
   * Do nothing to prevent dead lock
   */
  function handleFiles() {

  }
}
