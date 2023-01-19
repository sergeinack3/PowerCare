<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$object_class = CView::get("object_class", 'str');
$object_id    = CView::get("object_id", 'ref meta|object_class');

CView::checkin();

/** @var CCodable $object */
$object = new $object_class;
$object->load($object_id);

if ($object instanceof CSejour || $object instanceof COperation) {
  CAccessMedicalData::logAccess($object);
}

$anesth = new CMediusers();

$non_signes_activite_1 = 0;
$non_signes_activite_4 = 0;

$actes_ccam = $object->loadRefsActesCCAM();

if ($object instanceof CSejour) {
  $object->loadRefPraticien()->loadRefFunction();

  foreach ($actes_ccam as $_acte_ccam) {
    if ($_acte_ccam->code_activite == 4) {
      $anesth = $_acte_ccam->loadRefExecutant();
      break;
    }
  }
}

if ($object instanceof COperation) {
  $object->loadRefChir()->loadRefFunction();
  $object->loadRefPlageOp();

  if ($object->_ref_anesth) {
    $object->_ref_anesth->loadRefFunction();
  }
  $anesth = $object->_ref_anesth;

  if (!$anesth->_id) {
    foreach ($actes_ccam as $_acte_ccam) {
      if ($_acte_ccam->code_activite == 4) {
        $anesth = $_acte_ccam->loadRefExecutant();
        break;
      }
    }
  }
}

// Clôture possible que si tous les actes sont signés
foreach ($actes_ccam as $_acte_ccam) {
  if ($_acte_ccam->code_activite == 1 && !$_acte_ccam->signe) {
    $non_signes_activite_1 ++;
  }

  if ($_acte_ccam->code_activite == 4 && !$_acte_ccam->signe) {
    $non_signes_activite_4 ++;
  }
}

$smarty = new CSmartyDP;

$smarty->assign("object", $object);
$smarty->assign("anesth", $anesth);
$smarty->assign("non_signes_activite_1", $non_signes_activite_1);
$smarty->assign("non_signes_activite_4", $non_signes_activite_4);

$smarty->display("inc_cloture_activite.tpl");
