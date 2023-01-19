<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Personnel\CAffectationPersonnel;

global $m;

// Object binding
$obj = new CPlageOp();
$obj->bind($_POST);

$del    = CValue::post("del"    , 0);
$repeat = CValue::post("_repeat", 1);

$_iade_id         = CValue::post("_iade_id");
$_aideop_id       = CValue::post("_op_id");
$_op_panseuse_id  = CValue::post("_op_panseuse_id");
$_sagefemme_id    = CValue::post("_sagefemme_id");
$_manipulateur_id = CValue::post("_manipulateur_id");

$_del_iade_ids        = CValue::post("_del_iade_ids", array());
$_del_op_ids          = CValue::post("_del_op_ids", array());
$_del_op_panseuse_ids = CValue::post("_del_op_panseuse_ids", array());
$_del_sagefemme_ids   = CValue::post("_del_sagefemme_ids", array());
$_del_manipulateur_ids= CValue::post("_del_manipulateur_ids", array());

$del_personnel = array_merge($_del_iade_ids, $_del_op_ids, $_del_op_panseuse_ids, $_del_sagefemme_ids, $_del_manipulateur_ids);

// si l'id de l'objet est nul => creation
// si l'objet a un id, alors, modification

$body_msg = null;
$header   = array();
$msgNo    = null;

if ($del) {
  // Supression des plages
  $obj->load();
  while ($repeat > 0) {
    if (!$obj->_id) {
      CAppUI::setMsg("Plage non trouvée", UI_MSG_ERROR);
    }
    else {
      if ($msg = $obj->delete()) {
        CAppUI::setMsg("Plage non supprimée", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage supprimée", UI_MSG_OK);
      }
    }
    $repeat -= $obj->becomeNext();
  }
  $_SESSION["dPbloc"]["id"] = null;
  
}
else {
  //Modification des plages
  if ($obj->_id != 0) {
    $oldObj = new CPlageOp();
    $oldObj->load($obj->_id);
    $salle_id = $oldObj->salle_id;
    $chir_id  = $oldObj->chir_id;
    $spec_id  = $oldObj->spec_id;
    $secondary_function_id = $oldObj->secondary_function_id;
    $new_chir_id = $obj->chir_id;
    $new_spec_id = $obj->spec_id;
    while ($repeat > 0) {
      if ($obj->_id) {
        $obj->chir_id = $new_chir_id;
        $obj->spec_id = $new_spec_id;
        if ($msg = $obj->store()) {
          CAppUI::setMsg("Plage non mise à jour", UI_MSG_ERROR);
          CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
        }
        else {
          CAppUI::setMsg("Plage mise à jour", UI_MSG_OK);
        }
        managePersonnel($obj);
      }
      $repeat -= $obj->becomeNext($salle_id, $chir_id, $spec_id, $secondary_function_id);
    }
  }
  else {
    // Création des plages
    while ($repeat > 0) {
      
      if ($msg = $obj->store()) {
        CAppUI::setMsg("Plage non créée", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage créée", UI_MSG_OK);
      }
      
      managePersonnel($obj);
      $repeat -= $obj->becomeNext();
    }
  }
}

function managePersonnel($obj) {
  global $_iade_id, $_aideop_id, $_op_panseuse_id, $_sagefemme_id, $_manipulateur_id, $del_personnel;
  
  if ($_iade_id) {
    $affectation_personnel = new CAffectationPersonnel;
    $affectation_personnel->object_class = $obj->_class;
    $affectation_personnel->object_id    = $obj->_id;
    $affectation_personnel->personnel_id = $_iade_id;
    if ($msg = $affectation_personnel->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("IADE ajoutée", UI_MSG_OK);
    }
  }
  
  if ($_aideop_id) {
    $affectation_personnel = new CAffectationPersonnel;
    $affectation_personnel->object_class = $obj->_class;
    $affectation_personnel->object_id    = $obj->_id;
    $affectation_personnel->personnel_id = $_aideop_id;
    if ($msg = $affectation_personnel->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("Aide opératoire ajoutée", UI_MSG_OK);
    }
  }
  
  if ($_op_panseuse_id) {
    $affectation_personnel = new CAffectationPersonnel;
    $affectation_personnel->object_class = $obj->_class;
    $affectation_personnel->object_id    = $obj->_id;
    $affectation_personnel->personnel_id = $_op_panseuse_id;
    if ($msg = $affectation_personnel->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("Panseuse ajoutée", UI_MSG_OK);
    }
  }

  if ($_sagefemme_id) {
    $affectation_personnel = new CAffectationPersonnel;
    $affectation_personnel->object_class = $obj->_class;
    $affectation_personnel->object_id    = $obj->_id;
    $affectation_personnel->personnel_id = $_sagefemme_id;
    if ($msg = $affectation_personnel->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("Sage femme ajoutée", UI_MSG_OK);
    }
  }

  if ($_manipulateur_id) {
    $affectation_personnel = new CAffectationPersonnel;
    $affectation_personnel->object_class = $obj->_class;
    $affectation_personnel->object_id    = $obj->_id;
    $affectation_personnel->personnel_id = $_manipulateur_id;
    if ($msg = $affectation_personnel->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg("Manipulateur ajouté", UI_MSG_OK);
    }
  }

  foreach ($del_personnel as $_personnel_id) {
    if ($_personnel_id) {
      $affectation_personnel = new CAffectationPersonnel;
      $affectation_personnel->object_class = $obj->_class;
      $affectation_personnel->object_id = $obj->_id;
      $affectation_personnel->personnel_id = $_personnel_id;
      $affectation_personnel->loadMatchingObject();
      
      if ($affectation_personnel->_id) {
        if ($msg = $affectation_personnel->delete()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
        }
        else {
          CAppUI::setMsg("Personnel supprimé");
        }
      }
    }
  }
}
if ($ajax) {
  echo CAppUI::getMsg();
  CApp::rip();
}

if ($otherm = CValue::post("otherm", 0)) {
  $m = $otherm;
}

CAppUI::redirect("m=$m");
