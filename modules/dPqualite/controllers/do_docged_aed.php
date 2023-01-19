<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFileAddEdit;
use Ox\Mediboard\Qualite\CDocGed;

$doc_ged_id  = 0;
$file_id     = null;
$_validation = CValue::post("_validation", null);

class CDoDocGedAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CDocGed", "doc_ged_id");
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
    global $doc_ged_id, $file_id, $_validation;

    if ($this->_obj->doc_ged_id) {
      // Procédure Existante --> Verification

      //if ($this->_old->etat == CDocGed::REDAC && $_validation === null) {
      if (isset($_FILES["formfile"])) {
        // Test d'upload du fichier
        $objFile           = new CFileAddEdit();
        $objFile->redirect = null;
        $objFile->doIt();
        if (!CAppUI::isMsgOK()) {
          // Erreur sur le fichier !
          if ($this->redirectError) {
            $this->redirect =& $this->redirectError;
          }
          $this->doRedirect();
        }
        else {
          $file_id = $objFile->_obj->file_id;
        }
      }
    }

    if ($this->_old->group_id && $this->_obj->doc_chapitre_id && $this->_obj->doc_categorie_id && !$this->_old->num_ref) {
      // Nouvelle Procédure
      $this->_obj->version = 1;

      $where                     = array();
      $where["num_ref"]          = "IS NOT NULL";
      $where["group_id"]         = "= '" . $this->_old->group_id . "'";
      $where["doc_chapitre_id"]  = "= '" . $this->_obj->doc_chapitre_id . "'";
      $where["doc_categorie_id"] = "= '" . $this->_obj->doc_categorie_id . "'";
      $where["annule"]           = "= '0'";
      $order                     = "num_ref DESC";

      if ($this->_obj->num_ref) {
        // Numérotée manuellement
        $where["num_ref"] = "= '" . $this->_obj->num_ref . "'";
        $sameNumRef       = new CDocGed();
        $sameNumRef->loadObject($where, $order);
        if ($sameNumRef->_id) {
          $this->_obj->num_ref = null;
        }
      }
      else {
        // Pas de numéro : Récup n° dernier doc dans meme chapitre et catégorie
        $where["num_ref"] = "IS NOT NULL";
        $lastNumRef       = new CDocGed;
        $lastNumRef->loadObject($where, $order);
        if (!$lastNumRef->_id) {
          $this->_obj->num_ref = 1;
        }
        else {
          $this->_obj->num_ref = $lastNumRef->num_ref + 1;
        }
      }
    }

    if (!($this->_old->etat == CDocGed::VALID && $this->_obj->etat == CDocGed::TERMINE)) {
      // Annulation changement de version
      $this->_obj->version = $this->_old->version;
    }

    if ($msg = $this->_obj->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    }
    else {
      $this->redirect = null;
      $doc_ged_id     = $this->_obj->doc_ged_id;
    }
  }
}


class CDoDocGedSuiviAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CDocGedSuivi", "doc_ged_suivi_id");
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

    $this->_obj->date       = CValue::post("date") ?: CMbDT::datetime();
    $this->_obj->doc_ged_id = $doc_ged_id;
    if ($file_id !== null) {
      $this->_obj->file_id = $file_id;
    }
    if ($msg = $this->_obj->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      if ($this->redirectError) {
        $this->redirect =& $this->redirectError;
      }
    }
  }
}

$do1 = new CDoDocGedAddEdit();
$do1->doIt();

if (!$_validation) {
  $do2 = new CDoDocGedSuiviAddEdit();
  $do2->doIt();
}
