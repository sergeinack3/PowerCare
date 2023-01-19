<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFileAddEdit;

$doc_ged_id      = 0;
$file_id         = null;
$_firstModeleGed = CValue::post("_firstModeleGed", null);
$erreur_file     = null;

class CDoDocGedModeleAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CDocGed", "doc_ged_id");

    $this->createMsg = CAppUI::tr("msg-$this->className-create_modele");
    $this->modifyMsg = CAppUI::tr("msg-$this->className-modify_modele");
    $this->deleteMsg = CAppUI::tr("msg-$this->className-delete_modele");
  }

  /**
   * @inheritdoc
   */
  function doBind($reinstanciate_objects = false) {
    $this->ajax            = CValue::post("ajax");
    $this->suppressHeaders = CValue::post("suppressHeaders");
    $this->callBack        = CValue::post("callback");
    unset($_POST["ajax"]);
    unset($_POST["suppressHeaders"]);
    unset($_POST["callback"]);

    // Object binding
    $this->_obj->bind($_POST["ged"]);
    $this->_old->load($this->_obj->_id);
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    global $doc_ged_id, $file_id, $_firstModeleGed;

    $file_upload_ok = false;

    if ($msg = $this->_obj->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    }
    else {
      $this->redirect = null;
      $doc_ged_id     = $this->_obj->doc_ged_id;
      if (isset($_FILES["formfile"]) && $_FILES["formfile"]["name"] != "") {
        $objFile           = new CFileAddEdit;
        $objFile->redirect = null;
        $objFile->doBind();
        $_POST["object_id"] = $doc_ged_id;
        $objFile->dostore();
        if (CAppUI::isMsgOK()) {
          $file_upload_ok = true;
          $file_id        = $objFile->_obj->file_id;
        }
        else {
          // Erreur Upload
          if ($this->redirectError) {
            $this->redirect =& $this->redirectError;
          }
          $this->doRedirect();
        }
      }
    }
  }
}


class CDoDocGedModeleSuiviAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CDocGedSuivi", "doc_ged_suivi_id");

    $this->createMsg = CAppUI::tr("msg-$this->className-create_modele");
    $this->modifyMsg = CAppUI::tr("msg-$this->className-modify_modele");
    $this->deleteMsg = CAppUI::tr("msg-$this->className-delete_modele");
  }

  /**
   * @inheritdoc
   */
  function doBind($reinstanciate_objects = false) {

    $this->ajax            = CValue::post("ajax");
    $this->suppressHeaders = CValue::post("suppressHeaders");
    $this->callBack        = CValue::post("callback");
    unset($_POST["ajax"]);
    unset($_POST["suppressHeaders"]);
    unset($_POST["callback"]);

    // Object binding
    $this->_obj->bind($_POST["suivi"]);
    $this->_old->load($this->_obj->_id);
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    global $doc_ged_id, $file_id, $_validation;
    $this->_obj->date       = CMbDT::dateTime();
    $this->_obj->remarques  = CAppUI::tr("Modele");
    $this->_obj->doc_ged_id = $doc_ged_id;
    if ($file_id !== null) {
      $this->_obj->file_id          = $file_id;
      $this->_obj->doc_ged_suivi_id = null;
    }
    if ($msg = $this->_obj->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    }
  }
}


$do1 = new CDoDocGedModeleAddEdit;
if (!CCanDo::admin()) {
  $do1->doRedirect();
}
$do1->doIt();

if ($file_id) {
  $do2 = new CDoDocGedModeleSuiviAddEdit;
  $do2->doIt();
}
elseif ($_firstModeleGed) {
  $do1->doDelete();
  CAppUI::setMsg("CDocGed-msg-error_file", UI_MSG_ERROR);
}
