<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CCategoryPrescription;

/**
 * Import CSV des aides à la saisie
 */

CCanDo::checkRead();

$owner_guid   = CView::get("owner_guid", "str");
$object_class = CView::get("object_class", "str");

CView::checkin();

$file = isset($_FILES["import"]) ? $_FILES["import"] : null;
$owner = null;

$curr_user = CMediusers::get();
$owners = $curr_user->getOwners();

$owner = $owner_guid === CAppUI::tr("Instance") ? CCompteRendu::getInstanceObject() : CMbObject::loadFromGuid($owner_guid);
if ($file && $owner && $owner->_id && ($fp = fopen($file["tmp_name"], "r"))) {
  $user_id = $function_id = $group_id = null;

  switch ($owner->_class) {
    case "CMediusers":
      $user_id = $owner->_id;
      break;
    case "CFunctions":
      $function_id = $owner->_id;
      break;
    case "CGroups":
      $group_id = $owner->_id;
  }
  
  // Object columns on the first line
  $cols = fgetcsv($fp);
  // Remove chapitre key from db fields
  unset($cols[6]);

  // Each line
  while ($line = fgetcsv($fp)) {
    $aide = new CAideSaisie();
    foreach ($cols as $index => $field) {
      $aide->$field = $line[$index] === "" ? null : $line[$index];
    }
    
    $aide->user_id     = $user_id;
    $aide->function_id = $function_id;
    $aide->group_id    = $group_id;

    // Depend fields object mapping
    switch ($aide->class) {
      case "CTransmissionMedicale":
        if (isset($line["5"]) && isset($line[6]) && $line[6]) {
          $cat           = new CCategoryPrescription();
          $cat->nom      = $line[5];
          $cat->chapitre = $line[6];

          switch ($owner->_class) {
            case "CMediusers":
              $cat->group_id = $owner->loadRefFunction()->group_id;
              break;
            case "CFunctions":
              $cat->group_id = $owner->group_id;
            case "CGroups":
              $cat->group_id = $owner->_id;
            default:
          }

          if (!$cat->loadMatchingObjectEsc()) {
            $cat->store();
          }

          $aide->depend_value_2 = $cat->_id;
        }
        break;
      default:
    }

    $alreadyExists = $aide->loadMatchingObjectEsc();
    
    if ($msg = $aide->store()) {
      CAppUI::setMsg($msg);
      continue;
    }

    if ($alreadyExists) {
      CAppUI::setMsg("Aide à la saisie déjà présente");
    }
    else {
      CAppUI::setMsg("CAideSaisie-msg-create");
    }
  }
  fclose($fp);
  
  // Window refresh
  CAppUI::callbackAjax("window.opener.location.reload");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("owner"       , $owner);
$smarty->assign("owner_guid"  , $owner_guid);
$smarty->assign("object_class", $object_class);

$smarty->display("aides_import_csv");
