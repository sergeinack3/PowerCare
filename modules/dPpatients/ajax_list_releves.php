<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CRedon;
use Ox\Mediboard\Patients\CReleveRedon;

CCanDo::checkEdit();

$constante_medicale = CView::get("constante_medicale", "str");
$sejour_id          = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$redon = new CRedon();
$redon->constante_medicale = $constante_medicale;
$redon->sejour_id = $sejour_id;
$redon->loadMatchingObject();

$releves = $redon->loadRefsReleves();

CStoredObject::massLoadFwdRef($releves, "user_id");

/** @var CReleveRedon $_releve */
foreach ($releves as $_releve) {
  $_releve->getQteCumul();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("redon", $redon);

$smarty->display("inc_list_releves");